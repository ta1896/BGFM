<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$outputDocsFile = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'codebase-documentation.html';
$outputPublicFile = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'codebase-documentation.html';

$repoBaseUrl = detectRepositoryBaseUrl($root);
$repoBranch = detectRepositoryBranch($root) ?? 'main';

$includePaths = [
    'app',
    'bootstrap',
    'config',
    'database',
    'docs',
    'resources',
    'routes',
    'tests',
    'composer.json',
    'package.json',
    'README.md',
    'compose.yaml',
];

$excludePathPrefixes = [
    'bootstrap/cache',
    'docs/codebase-documentation.html',
    'public/codebase-documentation.html',
    'node_modules',
    'public/build',
    'storage',
    'vendor',
    '_reference_open_websoccer',
];

$sourceFileExtensions = [
    'php',
    'blade.php',
    'js',
    'css',
    'json',
    'md',
    'yml',
    'yaml',
    'xml',
];

$files = [];

foreach ($includePaths as $path) {
    $absolutePath = $root . DIRECTORY_SEPARATOR . $path;

    if (is_file($absolutePath)) {
        addFileToDocumentation(
            $files,
            $absolutePath,
            $root,
            $excludePathPrefixes,
            $sourceFileExtensions,
            $repoBaseUrl,
            $repoBranch
        );
        continue;
    }

    if (!is_dir($absolutePath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
            continue;
        }

        addFileToDocumentation(
            $files,
            $fileInfo->getPathname(),
            $root,
            $excludePathPrefixes,
            $sourceFileExtensions,
            $repoBaseUrl,
            $repoBranch
        );
    }
}

usort($files, static fn (array $a, array $b): int => strcmp($a['path'], $b['path']));

$summary = buildSummary($files);
$generatedAt = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

$html = renderHtml($files, $summary, $generatedAt, $repoBaseUrl, $repoBranch);

writeOutputFile($outputDocsFile, $html);
writeOutputFile($outputPublicFile, $html);

echo "Dokumentation erstellt: docs/codebase-documentation.html\n";
echo "Dokumentation erstellt: public/codebase-documentation.html\n";
echo "Dateien dokumentiert: " . count($files) . "\n";

if ($repoBaseUrl !== null) {
    echo "Repository-Linkbasis: {$repoBaseUrl} (Branch: {$repoBranch})\n";
}

function addFileToDocumentation(
    array &$files,
    string $absoluteFile,
    string $root,
    array $excludePathPrefixes,
    array $sourceFileExtensions,
    ?string $repoBaseUrl,
    string $repoBranch
): void {
    $relativePath = str_replace('\\', '/', ltrim(str_replace($root, '', $absoluteFile), DIRECTORY_SEPARATOR));

    foreach ($excludePathPrefixes as $prefix) {
        $normalizedPrefix = str_replace('\\', '/', $prefix);
        if ($relativePath === $normalizedPrefix || str_starts_with($relativePath, $normalizedPrefix . '/')) {
            return;
        }
    }

    if (!isSourceCodeFile($relativePath, $sourceFileExtensions)) {
        return;
    }

    $content = @file_get_contents($absoluteFile);
    if ($content === false) {
        return;
    }

    $lines = preg_split('/\R/u', $content) ?: [];
    $lineCount = count($lines);

    $module = strtok($relativePath, '/');
    if ($module === false || $module === '') {
        $module = 'root';
    }

    $fileType = detectFileType($relativePath);
    $symbols = extractSymbols($content, $fileType);
    $methods = extractMethodDocsFromSymbols($symbols, $relativePath);
    $serviceDoc = isServiceFile($relativePath, $fileType)
        ? buildServiceDocFromSymbols($relativePath, $symbols, $methods)
        : null;
    $repoFileUrl = buildRepositoryFileUrl($repoBaseUrl, $repoBranch, $relativePath);

    $files[] = [
        'path' => $relativePath,
        'module' => $module,
        'type' => $fileType,
        'description' => inferDescription($relativePath, $fileType),
        'size_bytes' => filesize($absoluteFile) ?: 0,
        'line_count' => $lineCount,
        'last_modified' => date('Y-m-d H:i:s', filemtime($absoluteFile) ?: time()),
        'symbols' => $symbols,
        'methods' => $methods,
        'service_doc' => $serviceDoc,
        'preview' => buildPreview($lines, 8),
        'repo_file_url' => $repoFileUrl,
    ];
}

function isSourceCodeFile(string $relativePath, array $extensions): bool
{
    foreach ($extensions as $extension) {
        if (str_ends_with($relativePath, '.' . $extension)) {
            return true;
        }
    }

    return false;
}

function detectFileType(string $relativePath): string
{
    if (str_ends_with($relativePath, '.blade.php')) {
        return 'blade';
    }
    if (str_ends_with($relativePath, '.php')) {
        return 'php';
    }
    if (str_ends_with($relativePath, '.js')) {
        return 'javascript';
    }
    if (str_ends_with($relativePath, '.css')) {
        return 'css';
    }
    if (str_ends_with($relativePath, '.json')) {
        return 'json';
    }
    if (str_ends_with($relativePath, '.md')) {
        return 'markdown';
    }
    if (str_ends_with($relativePath, '.yml') || str_ends_with($relativePath, '.yaml')) {
        return 'yaml';
    }
    if (str_ends_with($relativePath, '.xml')) {
        return 'xml';
    }

    return 'text';
}

function extractSymbols(string $content, string $fileType): array
{
    $symbols = [];

    if ($fileType === 'php' || $fileType === 'blade') {
        if (preg_match('/namespace\s+([^;]+);/u', $content, $namespaceMatch) === 1) {
            $symbols[] = 'namespace ' . trim($namespaceMatch[1]);
        }

        if (preg_match_all('/\b(class|interface|trait)\s+([A-Za-z0-9_]+)/u', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $symbols[] = $match[1] . ' ' . $match[2];
            }
        }

        if (preg_match_all('/\b(public|protected|private)\s+function\s+([A-Za-z0-9_]+)\s*\(/u', $content, $matches, PREG_SET_ORDER)) {
            $count = 0;
            foreach ($matches as $match) {
                $symbols[] = $match[1] . ' function ' . $match[2] . '()';
                $count++;
                if ($count >= 20) {
                    $symbols[] = '...';
                    break;
                }
            }
        }
    }

    if ($fileType === 'javascript') {
        if (preg_match_all('/\b(function|const|let)\s+([A-Za-z0-9_]+)/u', $content, $matches, PREG_SET_ORDER)) {
            foreach (array_slice($matches, 0, 20) as $match) {
                $symbols[] = $match[1] . ' ' . $match[2];
            }
        }
    }

    if ($fileType === 'css') {
        if (preg_match_all('/\.([a-zA-Z0-9\-_]+)\s*\{/u', $content, $matches)) {
            foreach (array_slice($matches[1], 0, 20) as $className) {
                $symbols[] = '.' . $className;
            }
        }
    }

    return array_values(array_unique($symbols));
}

function isServiceFile(string $relativePath, string $fileType): bool
{
    return $fileType === 'php' && str_contains($relativePath, '/Services/');
}

function extractMethodDocsFromSymbols(array $symbols, string $relativePath): array
{
    $methods = [];

    foreach ($symbols as $symbol) {
        if (preg_match('/^(public|protected|private)\s+function\s+([A-Za-z0-9_]+)\(\)$/u', $symbol, $matches) !== 1) {
            continue;
        }

        $name = (string) $matches[2];
        $methods[] = [
            'visibility' => strtolower((string) $matches[1]),
            'name' => $name,
            'signature' => strtolower((string) $matches[1]) . ' function ' . $name . '()',
            'purpose' => inferMethodPurpose($name, $relativePath),
        ];
    }

    return $methods;
}

function inferMethodPurpose(string $methodName, string $relativePath): string
{
    $name = strtolower($methodName);

    if ($name === '__construct') {
        return 'Initialisiert den Service mit seinen Abhaengigkeiten.';
    }
    if (str_starts_with($name, 'get') || str_starts_with($name, 'list') || str_starts_with($name, 'find')) {
        return 'Liefert Daten fuer den angefragten Kontext.';
    }
    if (str_starts_with($name, 'create') || str_starts_with($name, 'store') || str_starts_with($name, 'build')) {
        return 'Erzeugt oder baut ein fachliches Ergebnis auf.';
    }
    if (str_starts_with($name, 'update') || str_starts_with($name, 'set') || str_starts_with($name, 'sync')) {
        return 'Aktualisiert bestehenden Zustand und Daten.';
    }
    if (str_starts_with($name, 'delete') || str_starts_with($name, 'remove') || str_starts_with($name, 'clear')) {
        return 'Entfernt Eintraege oder setzt Daten zurueck.';
    }
    if (str_starts_with($name, 'simulate') || str_starts_with($name, 'process') || str_starts_with($name, 'run') || str_starts_with($name, 'execute') || str_starts_with($name, 'handle')) {
        return 'Fuehrt einen kompletten Ablaufprozess aus.';
    }
    if (str_starts_with($name, 'calculate') || str_starts_with($name, 'compute') || str_starts_with($name, 'aggregate')) {
        return 'Berechnet bzw. aggregiert Kennzahlen.';
    }
    if (str_starts_with($name, 'check') || str_starts_with($name, 'validate') || str_starts_with($name, 'can') || str_starts_with($name, 'is') || str_starts_with($name, 'has')) {
        return 'Prueft Regeln und liefert eine Entscheidung.';
    }
    if (str_starts_with($name, 'apply')) {
        return 'Wendet Regeln auf den aktuellen Kontext an.';
    }

    if (str_contains($relativePath, '/Services/')) {
        return 'Fachmethode innerhalb der Service-Logik.';
    }

    return 'Anwendungslogik dieser Datei.';
}

function buildServiceDocFromSymbols(string $relativePath, array $symbols, array $methods): array
{
    $namespace = '';
    $className = pathinfo($relativePath, PATHINFO_FILENAME);

    foreach ($symbols as $symbol) {
        if (str_starts_with($symbol, 'namespace ')) {
            $namespace = trim(substr($symbol, 10));
        }
        if (preg_match('/^(class|interface|trait)\s+([A-Za-z0-9_]+)/u', $symbol, $matches) === 1) {
            $className = (string) $matches[2];
            break;
        }
    }

    $publicMethods = array_values(array_filter(
        $methods,
        static fn (array $method): bool => $method['visibility'] === 'public' && $method['name'] !== '__construct'
    ));
    $features = inferServiceFeatures($relativePath, $publicMethods);

    return [
        'title' => $className,
        'features' => $features,
        'description' => 'Service fuer ' . ($features[0] ?? 'Allgemeine Domain-Logik') . '.',
        'usage_hint' => 'Nutze diesen Service fuer fachliche Operationen rund um ' . implode(', ', $features) . '.',
        'methods' => array_slice($publicMethods, 0, 10),
        'example' => buildServiceUsageExample($namespace, $className, $publicMethods),
        'anchor' => slugFromPath('service-' . $relativePath),
    ];
}

function inferServiceFeatures(string $relativePath, array $methods): array
{
    $haystack = strtolower($relativePath);
    foreach ($methods as $method) {
        $haystack .= ' ' . strtolower((string) $method['name']);
    }

    $featureMap = [
        'Live-Ticker & Matchflow' => ['live', 'ticker', 'match', 'simulate', 'processing'],
        'Aufstellung & Taktik' => ['lineup', 'formation', 'position'],
        'Wettbewerbe & Tabellen' => ['league', 'table', 'cup', 'competition', 'fixture', 'season'],
        'Spieler & Kader' => ['player', 'availability', 'training', 'club'],
        'Finanzen & Vertraege' => ['finance', 'contract', 'sponsor', 'reward'],
        'Transfer & Leihe' => ['transfer', 'loan', 'window'],
    ];

    $features = [];
    foreach ($featureMap as $label => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                $features[] = $label;
                break;
            }
        }
    }

    if ($features === []) {
        $features[] = 'Allgemeine Domain-Logik';
    }

    return $features;
}

function buildServiceUsageExample(string $namespace, string $className, array $methods): string
{
    $methodName = $methods[0]['name'] ?? 'handle';
    $resultLine = $methodName === 'handle'
        ? '$service->' . $methodName . '();'
        : '$result = $service->' . $methodName . '();';
    $fqn = $namespace !== '' ? $namespace . '\\' . $className : $className;

    return implode("\n", [
        'use ' . $fqn . ';',
        '',
        '$service = app(' . $className . '::class);',
        $resultLine,
    ]);
}

function slugFromPath(string $value): string
{
    $slug = strtolower($value);
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? $slug;
    $slug = trim($slug, '-');

    return $slug === '' ? 'entry' : $slug;
}

function inferDescription(string $relativePath, string $fileType): string
{
    if (str_contains($relativePath, '/Controllers/')) {
        return 'HTTP-Endpunkte und Request-Handling.';
    }
    if (str_contains($relativePath, '/Services/')) {
        return 'Geschaeftslogik und Domain-Operationen.';
    }
    if (str_contains($relativePath, '/Models/')) {
        return 'Eloquent-Modell und Persistenzabbildung.';
    }
    if (str_contains($relativePath, 'database/migrations/')) {
        return 'Schema-Migration fuer Datenbankstruktur.';
    }
    if (str_contains($relativePath, 'database/seeders/')) {
        return 'Seed-Daten und Initialisierung.';
    }
    if (str_contains($relativePath, 'tests/')) {
        return 'Automatisierte Testspezifikation.';
    }
    if (str_starts_with($relativePath, 'resources/views/')) {
        return 'Blade-View und UI-Darstellung.';
    }
    if (str_starts_with($relativePath, 'routes/')) {
        return 'Route- und Command-Definitionen.';
    }
    if (str_starts_with($relativePath, 'config/')) {
        return 'Applikationskonfiguration.';
    }
    if (str_starts_with($relativePath, 'resources/css/')) {
        return 'Styles und Design-System.';
    }
    if (str_starts_with($relativePath, 'resources/js/')) {
        return 'Clientseitige Interaktion und Bootstrap-Code.';
    }
    if (str_starts_with($relativePath, 'docs/')) {
        return 'Projekt- und Prozessdokumentation.';
    }
    if ($fileType === 'json') {
        return 'Manifest/Metadaten-Konfiguration.';
    }
    if ($fileType === 'markdown') {
        return 'Textdokumentation.';
    }

    return 'Projektdatei.';
}

function buildPreview(array $lines, int $maxLines): string
{
    $previewLines = [];
    foreach ($lines as $line) {
        $trimmed = trim((string) $line);
        if ($trimmed === '') {
            continue;
        }
        $previewLines[] = mb_substr($trimmed, 0, 180);
        if (count($previewLines) >= $maxLines) {
            break;
        }
    }

    return implode("\n", $previewLines);
}

function buildSummary(array $files): array
{
    $summary = [
        'file_count' => count($files),
        'line_count' => 0,
        'service_count' => 0,
        'method_count' => 0,
        'modules' => [],
        'types' => [],
    ];

    foreach ($files as $file) {
        $summary['line_count'] += (int) $file['line_count'];

        $module = (string) $file['module'];
        $type = (string) $file['type'];

        if (!isset($summary['modules'][$module])) {
            $summary['modules'][$module] = ['files' => 0, 'lines' => 0];
        }
        if (!isset($summary['types'][$type])) {
            $summary['types'][$type] = 0;
        }

        $summary['modules'][$module]['files']++;
        $summary['modules'][$module]['lines'] += (int) $file['line_count'];
        $summary['types'][$type]++;

        if (!empty($file['service_doc'])) {
            $summary['service_count']++;
        }
        if (!empty($file['methods']) && is_array($file['methods'])) {
            $summary['method_count'] += count($file['methods']);
        }
    }

    ksort($summary['modules']);
    ksort($summary['types']);

    return $summary;
}

function buildServiceCatalog(array $files): array
{
    $services = [];

    foreach ($files as $file) {
        if (empty($file['service_doc']) || !is_array($file['service_doc'])) {
            continue;
        }

        $serviceDoc = $file['service_doc'];
        $services[] = [
            'title' => (string) $serviceDoc['title'],
            'path' => (string) $file['path'],
            'description' => (string) $serviceDoc['description'],
            'usage_hint' => (string) $serviceDoc['usage_hint'],
            'features' => (array) $serviceDoc['features'],
            'methods' => (array) $serviceDoc['methods'],
            'example' => (string) $serviceDoc['example'],
            'anchor' => (string) $serviceDoc['anchor'],
            'file_anchor' => slugFromPath((string) $file['path']),
        ];
    }

    usort($services, static fn (array $a, array $b): int => strcmp($a['title'], $b['title']));

    return $services;
}

function buildQuickStartItems(array $serviceCatalog): array
{
    $items = [];
    $seen = [];

    foreach ($serviceCatalog as $service) {
        if (str_ends_with((string) $service['title'], 'Observer')) {
            continue;
        }

        foreach ($service['features'] as $feature) {
            if (isset($seen[$feature])) {
                continue;
            }

            $method = $service['methods'][0]['name'] ?? 'handle';
            $items[] = [
                'title' => $feature,
                'description' => 'Einstiegspunkt fuer dieses Feature ueber den passenden Service.',
                'service' => $service['title'],
                'method' => (string) $method,
            ];
            $seen[$feature] = true;
        }

        if (count($items) >= 6) {
            break;
        }
    }

    if ($items === []) {
        $items[] = [
            'title' => 'Code entdecken',
            'description' => 'Mit Suchfeld und Filtern den Einstieg finden.',
            'service' => 'n/a',
            'method' => 'n/a',
        ];
    }

    return $items;
}

function normalizeTagValue(string $value): string
{
    $normalized = strtolower(trim($value));
    $normalized = preg_replace('/[^a-z0-9]+/i', '-', $normalized) ?? $normalized;
    $normalized = trim($normalized, '-');

    return $normalized;
}

function renderHtml(array $files, array $summary, string $generatedAt, ?string $repoBaseUrl, string $repoBranch): string
{
    $moduleRows = '';
    foreach ($summary['modules'] as $module => $stats) {
        $moduleRows .= '<tr>'
            . '<td>' . h($module) . '</td>'
            . '<td>' . (int) $stats['files'] . '</td>'
            . '<td>' . (int) $stats['lines'] . '</td>'
            . '</tr>';
    }

    $typeBadges = '';
    foreach ($summary['types'] as $type => $count) {
        $typeBadges .= '<span class="badge">' . h($type) . ': ' . (int) $count . '</span>';
    }

    $repoInfo = '';
    if ($repoBaseUrl !== null) {
        $repoInfo = '<p>Repository: <a class="repo-link" href="' . h($repoBaseUrl) . '" target="_blank" rel="noopener noreferrer">'
            . h($repoBaseUrl)
            . '</a> (Branch: <code>' . h($repoBranch) . '</code>)</p>';
    }

    $serviceCatalog = buildServiceCatalog($files);
    $quickStartItems = buildQuickStartItems($serviceCatalog);
    $serviceTagMap = [];

    foreach ($serviceCatalog as $service) {
        foreach ($service['features'] as $feature) {
            $tagValue = normalizeTagValue((string) $feature);
            if ($tagValue === '') {
                continue;
            }
            $serviceTagMap[$tagValue] = (string) $feature;
        }
    }

    asort($serviceTagMap);

    $serviceTagOptions = '<option value="">Alle Tags</option>';
    $serviceTagChips = '';
    foreach ($serviceTagMap as $tagValue => $tagLabel) {
        $serviceTagOptions .= '<option value="' . h($tagValue) . '">' . h($tagLabel) . '</option>';
        $serviceTagChips .= '<button type="button" class="tag-chip js-service-tag-chip" data-tag="' . h($tagValue) . '">' . h($tagLabel) . '</button>';
    }

    $quickStartCards = '';
    foreach ($quickStartItems as $item) {
        $quickStartCards .= '<article class="guide-card">'
            . '<h3>' . h($item['title']) . '</h3>'
            . '<p>' . h($item['description']) . '</p>'
            . '<p><strong>Nutze:</strong> <code>' . h($item['service']) . '</code></p>'
            . '<p><strong>Start:</strong> <code>' . h($item['method']) . '()</code></p>'
            . '</article>';
    }

    $serviceCards = '';
    foreach ($serviceCatalog as $service) {
        $featureBadges = '';
        $serviceTagValues = [];
        foreach ($service['features'] as $feature) {
            $tagValue = normalizeTagValue((string) $feature);
            if ($tagValue !== '') {
                $serviceTagValues[] = $tagValue;
            }
            $featureBadges .= '<button type="button" class="badge badge-button js-service-tag-chip" data-tag="' . h($tagValue) . '">' . h($feature) . '</button>';
        }
        $serviceTagValues = array_values(array_unique($serviceTagValues));
        $serviceTagData = implode('|', $serviceTagValues);

        $methodRows = '';
        foreach ($service['methods'] as $method) {
            $methodRows .= '<tr>'
                . '<td><code>' . h((string) $method['signature']) . '</code></td>'
                . '<td>' . h((string) $method['purpose']) . '</td>'
                . '</tr>';
        }
        if ($methodRows === '') {
            $methodRows = '<tr><td colspan="2">Keine oeffentlichen Methoden erkannt.</td></tr>';
        }

        $serviceCards .= '<article class="service-card" data-feature="' . h(implode(' ', $service['features'])) . '" data-tags="' . h($serviceTagData) . '" id="' . h($service['anchor']) . '">'
            . '<header class="service-header">'
            . '<div>'
            . '<h3>' . h($service['title']) . '</h3>'
            . '<p class="service-path"><code>' . h($service['path']) . '</code></p>'
            . '</div>'
            . '<a class="service-jump" href="#' . h($service['file_anchor']) . '">Datei ansehen</a>'
            . '</header>'
            . '<p class="service-description">' . h($service['description']) . '</p>'
            . '<div class="feature-row">' . $featureBadges . '</div>'
            . '<div class="service-details">'
            . '<div><h4>Wann verwenden?</h4><p>' . h($service['usage_hint']) . '</p></div>'
            . '<div><h4>Beispiel</h4><pre>' . h($service['example']) . '</pre></div>'
            . '</div>'
            . '<h4>Methoden</h4>'
            . '<table class="method-table"><thead><tr><th>Methode</th><th>Erklaerung</th></tr></thead><tbody>' . $methodRows . '</tbody></table>'
            . '</article>';
    }
    if ($serviceCards === '') {
        $serviceCards = '<p>Keine Service-Klassen gefunden.</p>';
    }

    $methodIndexRows = '';
    foreach ($serviceCatalog as $service) {
        $serviceTagValues = [];
        foreach ($service['features'] as $feature) {
            $tagValue = normalizeTagValue((string) $feature);
            if ($tagValue !== '') {
                $serviceTagValues[] = $tagValue;
            }
        }
        $serviceTagValues = array_values(array_unique($serviceTagValues));
        $serviceTagData = implode('|', $serviceTagValues);

        foreach ($service['methods'] as $method) {
            $methodIndexRows .= '<tr data-tags="' . h($serviceTagData) . '">'
                . '<td><a class="repo-link" href="#' . h($service['anchor']) . '">' . h($service['title']) . '</a></td>'
                . '<td><code>' . h((string) $method['signature']) . '</code></td>'
                . '<td>' . h((string) $method['purpose']) . '</td>'
                . '<td>' . h((string) $method['visibility']) . '</td>'
                . '</tr>';
        }
    }
    if ($methodIndexRows === '') {
        $methodIndexRows = '<tr><td colspan="4">Keine Methoden im Index.</td></tr>';
    }

    $fileCards = '';
    foreach ($files as $file) {
        $symbols = '';
        foreach ($file['symbols'] as $symbol) {
            $symbols .= '<li>' . h($symbol) . '</li>';
        }
        if ($symbols === '') {
            $symbols = '<li>Keine expliziten Symbole erkannt.</li>';
        }

        $preview = trim((string) $file['preview']) === ''
            ? '<em>Kein Preview verfuegbar.</em>'
            : nl2br(h((string) $file['preview']));

        $repoLink = '';
        if (!empty($file['repo_file_url'])) {
            $repoLink = '<a class="repo-link" href="' . h((string) $file['repo_file_url']) . '" target="_blank" rel="noopener noreferrer">Repo-Datei</a>';
        }

        $methodList = '';
        if (!empty($file['methods']) && is_array($file['methods'])) {
            foreach (array_slice($file['methods'], 0, 15) as $method) {
                $methodList .= '<li><code>' . h((string) $method['signature']) . '</code> - ' . h((string) $method['purpose']) . '</li>';
            }
        }
        if ($methodList === '') {
            $methodList = '<li>Keine Methoden erkannt.</li>';
        }

        $fileCards .= '<article id="' . h(slugFromPath((string) $file['path'])) . '" class="file-card" data-path="' . h($file['path']) . '" data-module="' . h($file['module']) . '" data-type="' . h($file['type']) . '">'
            . '<header class="file-header">'
            . '<h3>' . h($file['path']) . '</h3>'
            . '<div class="file-meta">'
            . '<span class="badge">' . h($file['module']) . '</span>'
            . '<span class="badge">' . h($file['type']) . '</span>'
            . '<span class="badge">' . (int) $file['line_count'] . ' Zeilen</span>'
            . '<span class="badge">' . formatBytes((int) $file['size_bytes']) . '</span>'
            . '</div>'
            . '</header>'
            . '<p class="file-description">' . h($file['description']) . '</p>'
            . '<p class="file-modified">Letzte Aenderung: ' . h((string) $file['last_modified']) . '</p>'
            . ($repoLink !== '' ? '<p class="file-repo">' . $repoLink . '</p>' : '')
            . '<details>'
            . '<summary>Methoden, Symbole und Preview</summary>'
            . '<div class="details-grid">'
            . '<div><h4>Methoden</h4><ul>' . $methodList . '</ul><h4>Symbole</h4><ul>' . $symbols . '</ul></div>'
            . '<div><h4>Preview</h4><pre>' . $preview . '</pre></div>'
            . '</div>'
            . '</details>'
            . '</article>';
    }

    return '<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Codebase Dokumentation</title>
  <style>
    :root {
      color-scheme: dark;
      --bg: #0b1220;
      --surface: #111a2e;
      --surface-soft: #0f1728;
      --text: #e2e8f0;
      --muted: #93a4bc;
      --accent: #22d3ee;
      --border: #22314f;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "Manrope", "Segoe UI", sans-serif;
      background: radial-gradient(circle at top left, #1a2b55 0%, var(--bg) 55%);
      color: var(--text);
      line-height: 1.45;
    }
    .container {
      width: min(1280px, 94vw);
      margin: 2rem auto 3rem;
    }
    .top-nav {
      position: sticky;
      top: 0;
      z-index: 20;
      display: flex;
      flex-wrap: wrap;
      gap: .45rem;
      padding: .7rem;
      margin-bottom: 1rem;
      border: 1px solid var(--border);
      border-radius: 12px;
      background: rgba(15, 23, 40, 0.88);
      backdrop-filter: blur(6px);
    }
    .top-nav a {
      color: #c9f5ff;
      text-decoration: none;
      font-size: .84rem;
      border: 1px solid rgba(103, 232, 249, 0.42);
      border-radius: 999px;
      padding: .26rem .62rem;
    }
    .top-nav a:hover { background: rgba(103, 232, 249, 0.12); }
    .hero {
      background: linear-gradient(160deg, rgba(34, 211, 238, 0.14), rgba(15, 23, 40, 0.9));
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 1.25rem 1.5rem;
      margin-bottom: 1rem;
    }
    .section-card {
      background: rgba(17, 26, 46, 0.88);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .guide-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: .75rem;
    }
    .guide-card {
      border: 1px solid rgba(103, 232, 249, 0.25);
      background: rgba(12, 20, 36, 0.7);
      border-radius: 12px;
      padding: .75rem;
    }
    h1, h2, h3, h4 { margin: 0 0 .5rem; }
    h1 { font-size: 1.6rem; }
    h2 { font-size: 1.1rem; margin-top: 1.1rem; }
    h3 { font-size: 1rem; }
    p { margin: .35rem 0; color: var(--muted); }
    code {
      background: rgba(15, 23, 40, 0.8);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 0 .35rem;
    }
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: .6rem;
      margin-top: 1rem;
    }
    .stat-card {
      background: var(--surface-soft);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: .7rem .8rem;
    }
    .stat-value { font-size: 1.3rem; color: var(--text); font-weight: 700; }
    .badge {
      display: inline-flex;
      align-items: center;
      padding: .2rem .45rem;
      border: 1px solid var(--border);
      border-radius: 999px;
      margin-right: .35rem;
      margin-bottom: .35rem;
      color: #d1e6ff;
      font-size: .78rem;
      background: rgba(34, 211, 238, 0.08);
      white-space: nowrap;
    }
    .repo-link {
      color: #67e8f9;
      text-decoration: none;
      border-bottom: 1px dotted rgba(103, 232, 249, 0.5);
    }
    .repo-link:hover {
      color: #cffafe;
      border-bottom-color: rgba(207, 250, 254, 0.9);
    }
    .controls {
      display: grid;
      grid-template-columns: 1fr;
      gap: .6rem;
      margin: 1rem 0;
    }
    @media (min-width: 960px) {
      .controls {
        grid-template-columns: 1fr 220px 180px;
      }
    }
    input, select {
      width: 100%;
      background: var(--surface-soft);
      color: var(--text);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: .65rem .75rem;
    }
    .count-row {
      color: #bed6ff;
      margin-bottom: .5rem;
      font-size: .85rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--surface-soft);
      border: 1px solid var(--border);
      border-radius: 10px;
      overflow: hidden;
      margin: .75rem 0 1rem;
    }
    th, td {
      padding: .6rem .7rem;
      border-bottom: 1px solid var(--border);
      text-align: left;
      font-size: .9rem;
    }
    th { color: #d8ecff; font-weight: 600; }
    tbody tr:last-child td { border-bottom: none; }
    .services-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: .75rem;
    }
    .service-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: .85rem;
    }
    .service-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .5rem;
    }
    .service-path { margin-top: .1rem; font-size: .82rem; }
    .service-jump {
      color: #b5f0ff;
      font-size: .82rem;
      text-decoration: none;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: .3rem .5rem;
      white-space: nowrap;
    }
    .service-jump:hover { background: rgba(103, 232, 249, 0.13); }
    .service-description { color: #d4e3fb; }
    .feature-row { margin: .45rem 0; }
    .service-controls {
      display: grid;
      grid-template-columns: 1fr;
      gap: .6rem;
      margin-bottom: .75rem;
    }
    @media (min-width: 920px) {
      .service-controls {
        grid-template-columns: 260px 1fr;
      }
    }
    .tag-chip-row {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
    }
    .tag-chip {
      background: rgba(34, 211, 238, 0.08);
      color: #d1e6ff;
      border: 1px solid var(--border);
      border-radius: 999px;
      padding: .2rem .6rem;
      font-size: .78rem;
      cursor: pointer;
    }
    .tag-chip:hover { background: rgba(34, 211, 238, 0.2); }
    .tag-chip.is-active {
      background: rgba(34, 211, 238, 0.3);
      border-color: rgba(103, 232, 249, 0.8);
      color: #ecfeff;
    }
    .badge-button {
      cursor: pointer;
      font-family: inherit;
    }
    .service-count-row {
      color: #bed6ff;
      margin-bottom: .45rem;
      font-size: .84rem;
    }
    .service-details {
      display: grid;
      grid-template-columns: 1fr;
      gap: .75rem;
      margin-top: .5rem;
      margin-bottom: .5rem;
    }
    .method-table td, .method-table th { font-size: .84rem; }
    @media (min-width: 920px) {
      .service-details { grid-template-columns: 1fr 1fr; }
    }
    .file-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: .75rem;
    }
    .file-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: .85rem;
    }
    .file-header {
      display: flex;
      flex-direction: column;
      gap: .4rem;
      margin-bottom: .35rem;
    }
    .file-header h3 {
      font-family: "Consolas", "Courier New", monospace;
      font-size: .9rem;
      overflow-wrap: anywhere;
    }
    .file-meta {
      display: flex;
      flex-wrap: wrap;
      gap: .2rem;
    }
    .file-description {
      color: #c8d5ea;
      margin-bottom: .3rem;
    }
    .file-modified {
      color: #7d91ac;
      font-size: .78rem;
      margin-bottom: .2rem;
    }
    .file-repo {
      margin-bottom: .4rem;
      font-size: .82rem;
    }
    details {
      border-top: 1px solid var(--border);
      padding-top: .5rem;
      margin-top: .45rem;
    }
    summary {
      cursor: pointer;
      color: #b6d4ff;
      font-weight: 600;
      margin-bottom: .5rem;
    }
    .details-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: .75rem;
    }
    @media (min-width: 940px) {
      .details-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    ul {
      margin: .2rem 0;
      padding-left: 1rem;
    }
    li { margin: .2rem 0; color: #c7d7ef; }
    pre {
      margin: 0;
      white-space: pre-wrap;
      word-break: break-word;
      background: #0c1424;
      border: 1px solid #1f314f;
      border-radius: 8px;
      padding: .55rem;
      color: #d6e6ff;
      font-size: .8rem;
      max-height: 220px;
      overflow: auto;
    }
    .footer {
      margin-top: 1.2rem;
      color: #7f93ad;
      font-size: .8rem;
    }
  </style>
</head>
<body>
  <main class="container">
    <nav class="top-nav">
      <a href="#top">Uebersicht</a>
      <a href="#quickstart">Schnellstart</a>
      <a href="#services">Service-Katalog</a>
      <a href="#methods">Methoden-Index</a>
      <a href="#modules">Module</a>
      <a href="#files">Dateien</a>
    </nav>

    <section id="top" class="hero">
      <h1>Codebase Dokumentation (extern)</h1>
      <p>Automatisch generierte Gesamtdokumentation der Projektdateien mit Service-Erklaerungen, Methoden-Index und Navigation.</p>
      <p>Generiert am: ' . h($generatedAt) . '</p>
      ' . $repoInfo . '
      <div class="stats">
        <div class="stat-card"><div class="stat-value">' . (int) $summary['file_count'] . '</div><div>Dateien dokumentiert</div></div>
        <div class="stat-card"><div class="stat-value">' . (int) $summary['line_count'] . '</div><div>Zeilen gesamt</div></div>
        <div class="stat-card"><div class="stat-value">' . (int) $summary['service_count'] . '</div><div>Services</div></div>
        <div class="stat-card"><div class="stat-value">' . (int) $summary['method_count'] . '</div><div>Methoden erkannt</div></div>
        <div class="stat-card"><div class="stat-value">' . count($summary['modules']) . '</div><div>Module</div></div>
        <div class="stat-card"><div class="stat-value">' . count($summary['types']) . '</div><div>Dateitypen</div></div>
      </div>
      <h2>Dateitypen</h2>
      <div>' . $typeBadges . '</div>
    </section>

    <section id="quickstart" class="section-card">
      <h2>Was kann ich damit machen?</h2>
      <p>Die Karten zeigen, welcher Service fuer welches Feature verwendet wird und welche Methode dein Einstieg ist.</p>
      <div class="guide-grid">' . $quickStartCards . '</div>
    </section>

    <section id="services" class="section-card">
      <h2>Service-Katalog</h2>
      <p>Service-Referenz mit Einsatzhinweis, Erklaerung und Beispielnutzung.</p>
      <div class="service-controls">
        <select id="serviceTagFilter">' . $serviceTagOptions . '</select>
        <div id="serviceTagChips" class="tag-chip-row">' . $serviceTagChips . '</div>
      </div>
      <p id="serviceVisibleCount" class="service-count-row"></p>
      <div class="services-grid">' . $serviceCards . '</div>
    </section>

    <section id="methods" class="section-card">
      <h2>Methoden-Index</h2>
      <p id="methodVisibleCount" class="service-count-row"></p>
      <table class="method-table">
        <thead><tr><th>Service</th><th>Methode</th><th>Erklaerung</th><th>Sichtbarkeit</th></tr></thead>
        <tbody id="methodIndexBody">' . $methodIndexRows . '</tbody>
      </table>
    </section>

    <section class="controls section-card">
      <input id="searchInput" type="text" placeholder="Dateipfad, Symbol oder Beschreibung suchen...">
      <select id="moduleFilter"><option value="">Alle Module</option></select>
      <select id="typeFilter"><option value="">Alle Typen</option></select>
    </section>

    <section id="modules" class="section-card">
      <h2>Moduluebersicht</h2>
      <table>
        <thead><tr><th>Modul</th><th>Dateien</th><th>Zeilen</th></tr></thead>
        <tbody>' . $moduleRows . '</tbody>
      </table>
    </section>

    <section id="files" class="section-card">
      <h2>Dateidokumentation</h2>
      <p id="visibleCount" class="count-row"></p>
      <div id="fileGrid" class="file-grid">' . $fileCards . '</div>
    </section>

    <p class="footer">Hinweis: Diese Seite dokumentiert Projektcode und Konfiguration, nicht Abhaengigkeiten aus <code>vendor/</code> oder <code>node_modules/</code>.</p>
  </main>

  <script>
    (() => {
      const searchInput = document.getElementById("searchInput");
      const moduleFilter = document.getElementById("moduleFilter");
      const typeFilter = document.getElementById("typeFilter");
      const cards = Array.from(document.querySelectorAll(".file-card"));
      const visibleCount = document.getElementById("visibleCount");
      const serviceTagFilter = document.getElementById("serviceTagFilter");
      const serviceCards = Array.from(document.querySelectorAll(".service-card"));
      const serviceTagChips = Array.from(document.querySelectorAll(".js-service-tag-chip"));
      const serviceVisibleCount = document.getElementById("serviceVisibleCount");
      const methodRows = Array.from(document.querySelectorAll("#methodIndexBody tr[data-tags]"));
      const methodVisibleCount = document.getElementById("methodVisibleCount");

      const modules = [...new Set(cards.map((card) => card.dataset.module))].sort();
      const types = [...new Set(cards.map((card) => card.dataset.type))].sort();

      for (const module of modules) {
        const option = document.createElement("option");
        option.value = module;
        option.textContent = module;
        moduleFilter.appendChild(option);
      }

      for (const type of types) {
        const option = document.createElement("option");
        option.value = type;
        option.textContent = type;
        typeFilter.appendChild(option);
      }

      const normalize = (value) => (value || "").toLowerCase();
      const splitTags = (value) => (value || "").split("|").filter(Boolean);
      const hasTag = (dataTags, selectedTag) => selectedTag === "" || splitTags(dataTags).includes(selectedTag);

      const applyFilters = () => {
        const query = normalize(searchInput.value);
        const selectedModule = moduleFilter.value;
        const selectedType = typeFilter.value;
        let visible = 0;

        for (const card of cards) {
          const moduleMatch = selectedModule === "" || card.dataset.module === selectedModule;
          const typeMatch = selectedType === "" || card.dataset.type === selectedType;
          const textMatch = query === "" || normalize(card.innerText).includes(query);
          const isVisible = moduleMatch && typeMatch && textMatch;
          card.style.display = isVisible ? "" : "none";
          if (isVisible) {
            visible++;
          }
        }

        if (visibleCount) {
          visibleCount.textContent = "Sichtbare Dateien: " + visible + " / " + cards.length;
        }
      };

      const applyServiceTagFilter = () => {
        if (!serviceTagFilter) {
          return;
        }

        const selectedTag = serviceTagFilter.value;
        let visibleServices = 0;
        let visibleMethods = 0;

        for (const card of serviceCards) {
          const isVisible = hasTag(card.dataset.tags, selectedTag);
          card.style.display = isVisible ? "" : "none";
          if (isVisible) {
            visibleServices++;
          }
        }

        for (const row of methodRows) {
          const isVisible = hasTag(row.dataset.tags, selectedTag);
          row.style.display = isVisible ? "" : "none";
          if (isVisible) {
            visibleMethods++;
          }
        }

        if (serviceVisibleCount) {
          serviceVisibleCount.textContent = "Sichtbare Services: " + visibleServices + " / " + serviceCards.length;
        }
        if (methodVisibleCount) {
          methodVisibleCount.textContent = "Sichtbare Methoden: " + visibleMethods + " / " + methodRows.length;
        }

        for (const chip of serviceTagChips) {
          const isActive = selectedTag !== "" && chip.dataset.tag === selectedTag;
          chip.classList.toggle("is-active", isActive);
        }
      };

      searchInput.addEventListener("input", applyFilters);
      moduleFilter.addEventListener("change", applyFilters);
      typeFilter.addEventListener("change", applyFilters);
      if (serviceTagFilter) {
        serviceTagFilter.addEventListener("change", applyServiceTagFilter);
      }
      for (const chip of serviceTagChips) {
        chip.addEventListener("click", () => {
          if (!serviceTagFilter) {
            return;
          }
          const selected = chip.dataset.tag || "";
          serviceTagFilter.value = serviceTagFilter.value === selected ? "" : selected;
          applyServiceTagFilter();
        });
      }
      applyFilters();
      applyServiceTagFilter();
    })();
  </script>
</body>
</html>';
}

function writeOutputFile(string $path, string $content): void
{
    $directory = dirname($path);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    file_put_contents($path, $content);
}

function buildRepositoryFileUrl(?string $baseUrl, string $branch, string $relativePath): ?string
{
    if ($baseUrl === null) {
        return null;
    }

    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $relativePath)));

    return rtrim($baseUrl, '/') . '/blob/' . rawurlencode($branch) . '/' . $encodedPath;
}

function detectRepositoryBaseUrl(string $root): ?string
{
    $remote = trim((string) runGitCommand($root, 'git remote get-url origin 2>nul'));
    if ($remote === '') {
        return null;
    }

    if (str_starts_with($remote, 'git@')) {
        if (preg_match('/^git@([^:]+):(.+)$/', $remote, $matches) === 1) {
            $remote = 'https://' . $matches[1] . '/' . $matches[2];
        }
    } elseif (str_starts_with($remote, 'ssh://git@')) {
        $remote = preg_replace('/^ssh:\/\/git@/i', 'https://', $remote) ?? $remote;
    }

    $remote = rtrim($remote, '/');
    if (str_ends_with(strtolower($remote), '.git')) {
        $remote = substr($remote, 0, -4);
    }

    if (!str_starts_with($remote, 'http://') && !str_starts_with($remote, 'https://')) {
        return null;
    }

    return $remote;
}

function detectRepositoryBranch(string $root): ?string
{
    $branch = trim((string) runGitCommand($root, 'git rev-parse --abbrev-ref HEAD 2>nul'));
    if ($branch === '' || $branch === 'HEAD') {
        return null;
    }

    return $branch;
}

function runGitCommand(string $root, string $command): ?string
{
    $cwd = getcwd();
    if ($cwd === false) {
        return null;
    }

    chdir($root);
    $output = shell_exec($command);
    chdir($cwd);

    return $output;
}

function formatBytes(int $bytes): string
{
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $power = (int) floor(log($bytes, 1024));
    $power = min($power, count($units) - 1);
    $value = $bytes / (1024 ** $power);

    return number_format($value, $power === 0 ? 0 : 2, '.', '') . ' ' . $units[$power];
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
