<?php

namespace App\Modules;

use App\Models\SystemModule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Throwable;

class ModuleManager
{
    private ?Collection $discovered = null;

    private bool $providersRegistered = false;

    private bool $routesLoaded = false;

    private ?bool $moduleTableAvailable = null;

    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    public function registerProviders($app): void
    {
        if ($this->providersRegistered) {
            return;
        }

        foreach ($this->enabledModules(false) as $module) {
            foreach ($module['providers'] as $providerClass) {
                if (is_string($providerClass) && class_exists($providerClass)) {
                    $app->register($providerClass);
                }
            }
        }

        $this->providersRegistered = true;
    }

    public function boot(ServiceProvider $provider): void
    {
        $this->syncManifestToDatabase();

        if ((bool) config('modules.autoload_migrations', true)) {
            foreach ($this->enabledModules() as $module) {
                if ($module['migration_path'] && $this->files->isDirectory($module['migration_path'])) {
                    $path = $module['migration_path'];
                    app()->afterResolving('migrator', function ($migrator) use ($path) {
                        $migrator->path($path);
                    });
                }
            }
        }

        if ((bool) config('modules.autoload_routes', true)) {
            $this->loadRoutes();
        }
    }

    public function frontendRegistry(): array
    {
        $managerNavigation = [];
        $managerWithClubNavigation = [];
        $managerWithoutClubNavigation = [];
        $adminNavigation = [];
        $dashboardWidgets = [];
        $settingsSections = [];
        $playerActions = [];
        $matchcenterPanels = [];
        $notifications = [];

        foreach ($this->enabledModules() as $module) {
            $frontend = $module['frontend'];
            $managerNavigation = array_merge($managerNavigation, $frontend['manager_navigation'] ?? []);
            $managerWithClubNavigation = array_merge($managerWithClubNavigation, $frontend['manager_with_club_navigation'] ?? []);
            $managerWithoutClubNavigation = array_merge($managerWithoutClubNavigation, $frontend['manager_without_club_navigation'] ?? []);
            $adminNavigation = array_merge($adminNavigation, $frontend['admin_navigation'] ?? []);
            $dashboardWidgets = array_merge($dashboardWidgets, $this->normalizeDashboardWidgets($this->filterHookEntries($frontend['dashboard_widgets'] ?? [])));
            $settingsSections = array_merge($settingsSections, $this->settingsSectionsForModule($module));
            $playerActions = array_merge($playerActions, $this->normalizePlayerActions($this->filterHookEntries($frontend['player_actions'] ?? [])));
            $matchcenterPanels = array_merge($matchcenterPanels, $this->normalizeMatchcenterPanels($this->filterHookEntries($frontend['matchcenter_panels'] ?? [])));
            $notifications = array_merge($notifications, $this->normalizeNotifications($this->filterHookEntries($frontend['notifications'] ?? [])));
        }

        return [
            'enabled' => $this->enabledModules()
                ->map(fn (array $module) => [
                    'key' => $module['key'],
                    'name' => $module['name'],
                    'version' => $module['version'],
                ])
                ->values()
                ->all(),
            'manager_navigation' => $managerNavigation,
            'manager_with_club_navigation' => $managerWithClubNavigation,
            'manager_without_club_navigation' => $managerWithoutClubNavigation,
            'admin_navigation' => $adminNavigation,
            'dashboard_widgets' => $dashboardWidgets,
            'settings_sections' => $settingsSections,
            'player_actions' => $playerActions,
            'matchcenter_panels' => $matchcenterPanels,
            'notifications' => $notifications,
        ];
    }

    public function adminRegistry(): array
    {
        $stateMap = $this->moduleStateMap();

        return $this->discoveredModules()
            ->map(function (array $module) use ($stateMap): array {
                $databaseEnabled = array_key_exists($module['key'], $stateMap) ? (bool) $stateMap[$module['key']] : null;
                $effectiveEnabled = $databaseEnabled ?? (bool) $module['enabled_by_default'];

                return [
                    'key' => $module['key'],
                    'name' => $module['name'],
                    'version' => $module['version'],
                    'description' => $module['description'],
                    'enabled' => $effectiveEnabled,
                    'enabled_by_default' => (bool) $module['enabled_by_default'],
                    'source' => $databaseEnabled === null ? 'manifest' : 'database',
                    'has_routes' => (bool) ($module['route_path'] && $this->files->exists($module['route_path'])),
                    'has_migrations' => (bool) ($module['migration_path'] && $this->files->isDirectory($module['migration_path'])),
                    'provider_count' => count($module['providers']),
                    'manager_navigation_groups' => count($module['frontend']['manager_navigation'] ?? []),
                    'admin_navigation_groups' => count($module['frontend']['admin_navigation'] ?? []),
                    'dashboard_widget_count' => count($module['frontend']['dashboard_widgets'] ?? []),
                    'settings_section_count' => count($module['frontend']['settings_sections'] ?? []),
                    'player_action_count' => count($module['frontend']['player_actions'] ?? []),
                    'matchcenter_panel_count' => count($module['frontend']['matchcenter_panels'] ?? []),
                    'notification_hook_count' => count($module['frontend']['notifications'] ?? []),
                    'module_path' => $module['module_path'],
                ];
            })
            ->values()
            ->all();
    }

    public function settingsFieldDefinitions(): array
    {
        $fields = [];

        foreach ($this->enabledModules() as $module) {
            foreach ($this->settingsSectionsForModule($module) as $section) {
                foreach (($section['fields'] ?? []) as $field) {
                    if (!is_string($field['key'] ?? null)) {
                        continue;
                    }

                    $fields[(string) $field['key']] = $field;
                }
            }
        }

        return $fields;
    }

    public function setEnabled(string $key, bool $enabled): void
    {
        if (!$this->hasModuleTable()) {
            return;
        }

        $module = $this->discoveredModules()->firstWhere('key', $key);

        if (!$module) {
            return;
        }

        SystemModule::query()->updateOrCreate(
            ['key' => $module['key']],
            [
                'name' => $module['name'],
                'version' => $module['version'],
                'description' => $module['description'],
                'enabled' => $enabled,
                'module_path' => $module['module_path'],
            ]
        );
    }

    public function discoveredModules(): Collection
    {
        if ($this->discovered !== null) {
            return $this->discovered;
        }

        $modules = collect();

        foreach ((array) config('modules.paths', []) as $basePath) {
            if (!$this->files->isDirectory($basePath)) {
                continue;
            }

            foreach ($this->files->directories($basePath) as $modulePath) {
                $manifestPath = $modulePath.DIRECTORY_SEPARATOR.'module.json';
                if (!$this->files->exists($manifestPath)) {
                    continue;
                }

                $decoded = json_decode($this->files->get($manifestPath), true);
                if (!is_array($decoded)) {
                    continue;
                }

                $key = (string) ($decoded['key'] ?? Str::slug(basename($modulePath)));
                $modules->push([
                    'key' => $key,
                    'name' => (string) ($decoded['name'] ?? Str::headline($key)),
                    'version' => (string) ($decoded['version'] ?? '1.0.0'),
                    'description' => (string) ($decoded['description'] ?? ''),
                    'enabled_by_default' => (bool) ($decoded['enabled'] ?? false),
                    'providers' => array_values(array_filter((array) ($decoded['providers'] ?? []), 'is_string')),
                    'frontend' => $this->normalizeFrontendRegistry($decoded['frontend'] ?? []),
                    'module_path' => $modulePath,
                    'route_path' => isset($decoded['routes']) ? $modulePath.DIRECTORY_SEPARATOR.(string) $decoded['routes'] : null,
                    'migration_path' => isset($decoded['migrations']) ? $modulePath.DIRECTORY_SEPARATOR.(string) $decoded['migrations'] : null,
                ]);
            }
        }

        return $this->discovered = $modules->sortBy('name')->values();
    }

    public function enabledModules(bool $useDatabaseState = true): Collection
    {
        $stateMap = $useDatabaseState ? $this->moduleStateMap() : [];

        return $this->discoveredModules()
            ->filter(function (array $module) use ($stateMap): bool {
                if (array_key_exists($module['key'], $stateMap)) {
                    return (bool) $stateMap[$module['key']];
                }

                return (bool) $module['enabled_by_default'];
            })
            ->values();
    }

    private function loadRoutes(): void
    {
        if ($this->routesLoaded || app()->routesAreCached()) {
            return;
        }

        foreach ($this->enabledModules() as $module) {
            if ($module['route_path'] && $this->files->exists($module['route_path'])) {
                Route::middleware('web')->group($module['route_path']);
            }
        }

        $this->routesLoaded = true;
    }

    private function syncManifestToDatabase(): void
    {
        if (!(bool) config('modules.sync_manifest_to_database', true) || !$this->hasModuleTable()) {
            return;
        }

        foreach ($this->discoveredModules() as $module) {
            SystemModule::query()->updateOrCreate(
                ['key' => $module['key']],
                [
                    'name' => $module['name'],
                    'version' => $module['version'],
                    'description' => $module['description'],
                    'enabled' => SystemModule::query()->where('key', $module['key'])->value('enabled') ?? $module['enabled_by_default'],
                    'module_path' => $module['module_path'],
                ]
            );
        }
    }

    private function moduleStateMap(): array
    {
        if (!$this->hasModuleTable()) {
            return [];
        }

        return SystemModule::query()
            ->pluck('enabled', 'key')
            ->map(fn ($enabled) => (bool) $enabled)
            ->all();
    }

    private function hasModuleTable(): bool
    {
        if ($this->moduleTableAvailable !== null) {
            return $this->moduleTableAvailable;
        }

        try {
            return $this->moduleTableAvailable = Schema::hasTable('system_modules');
        } catch (Throwable) {
            return $this->moduleTableAvailable = false;
        }
    }

    private function settingsSectionsForModule(array $module): array
    {
        return collect($module['frontend']['settings_sections'] ?? [])
            ->filter(fn ($section) => is_array($section))
            ->map(function (array $section) use ($module): array {
                return [
                    ...$section,
                    'module_key' => $module['key'],
                    'module_name' => $module['name'],
                    'fields' => collect($section['fields'] ?? [])
                        ->filter(fn ($field) => is_array($field) && is_string($field['key'] ?? null))
                        ->map(function (array $field): array {
                            return [
                                ...$field,
                                'value' => config((string) $field['key'], $field['default'] ?? null),
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function filterHookEntries(array $entries): array
    {
        return collect($entries)
            ->filter(fn ($entry) => is_array($entry))
            ->filter(function (array $entry): bool {
                $enabledWhen = $entry['enabled_when'] ?? null;
                if (!is_string($enabledWhen) || $enabledWhen === '') {
                    return true;
                }

                return (bool) config($enabledWhen, $entry['enabled_default'] ?? true);
            })
            ->values()
            ->all();
    }

    private function normalizePlayerActions(array $entries): array
    {
        $allowedMethods = ['get', 'post', 'put', 'patch', 'delete'];
        $allowedScopes = ['all', 'owned_only', 'external_only'];
        $allowedPlacements = ['overview', 'customize', 'history'];

        return collect($entries)
            ->map(function (array $entry) use ($allowedMethods, $allowedScopes, $allowedPlacements): ?array {
                $route = $entry['route'] ?? null;
                $title = trim((string) ($entry['title'] ?? ''));

                if (!is_string($route) || $route === '' || $title === '') {
                    return null;
                }

                $method = strtolower((string) ($entry['method'] ?? 'get'));
                $scope = (string) ($entry['scope'] ?? 'all');
                $placement = (string) ($entry['placement'] ?? 'overview');

                return [
                    'key' => (string) ($entry['key'] ?? Str::slug($title)),
                    'title' => $title,
                    'description' => trim((string) ($entry['description'] ?? '')),
                    'route' => $route,
                    'method' => in_array($method, $allowedMethods, true) ? $method : 'get',
                    'accent' => (string) ($entry['accent'] ?? 'slate'),
                    'icon' => (string) ($entry['icon'] ?? 'gear'),
                    'scope' => in_array($scope, $allowedScopes, true) ? $scope : 'all',
                    'placement' => in_array($placement, $allowedPlacements, true) ? $placement : 'overview',
                    'payload' => is_array($entry['payload'] ?? null) ? $entry['payload'] : [],
                    'query' => is_array($entry['query'] ?? null) ? $entry['query'] : [],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeDashboardWidgets(array $entries): array
    {
        $allowedPlacements = ['main', 'sidebar'];

        return collect($entries)
            ->map(function (array $entry) use ($allowedPlacements): ?array {
                $route = $entry['route'] ?? null;
                $title = trim((string) ($entry['title'] ?? ''));

                if (!is_string($route) || $route === '' || $title === '') {
                    return null;
                }

                $placement = (string) ($entry['placement'] ?? 'main');

                return [
                    'key' => (string) ($entry['key'] ?? Str::slug($title)),
                    'title' => $title,
                    'description' => trim((string) ($entry['description'] ?? '')),
                    'route' => $route,
                    'accent' => (string) ($entry['accent'] ?? 'slate'),
                    'icon' => (string) ($entry['icon'] ?? 'gear'),
                    'placement' => in_array($placement, $allowedPlacements, true) ? $placement : 'main',
                    'priority' => (int) ($entry['priority'] ?? 999),
                ];
            })
            ->filter()
            ->sortBy('priority')
            ->values()
            ->all();
    }

    private function normalizeMatchcenterPanels(array $entries): array
    {
        return collect($entries)
            ->map(function (array $entry): ?array {
                $key = $entry['key'] ?? null;
                $title = trim((string) ($entry['title'] ?? ''));

                if (!is_string($key) || $key === '' || $title === '') {
                    return null;
                }

                return [
                    'key' => $key,
                    'title' => $title,
                    'description' => trim((string) ($entry['description'] ?? '')),
                    'route' => is_string($entry['route'] ?? null) ? (string) $entry['route'] : null,
                    'accent' => (string) ($entry['accent'] ?? 'cyan'),
                    'icon' => (string) ($entry['icon'] ?? 'gear'),
                    'priority' => (int) ($entry['priority'] ?? 999),
                ];
            })
            ->filter()
            ->sortBy('priority')
            ->values()
            ->all();
    }

    private function normalizeNotifications(array $entries): array
    {
        return collect($entries)
            ->map(function (array $entry): ?array {
                $type = $entry['type'] ?? null;
                $label = trim((string) ($entry['label'] ?? ''));

                if (!is_string($type) || $type === '' || $label === '') {
                    return null;
                }

                return [
                    'type' => $type,
                    'label' => $label,
                    'accent' => trim((string) ($entry['accent'] ?? 'border-l-slate-500')),
                    'icon' => trim((string) ($entry['icon'] ?? 'gear')),
                    'icon_wrap' => trim((string) ($entry['icon_wrap'] ?? 'border border-white/10 bg-white/[0.03] text-white')),
                    'badge' => trim((string) ($entry['badge'] ?? 'border border-white/10 bg-white/[0.03] text-white')),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeFrontendRegistry(mixed $frontend): array
    {
        $frontend = is_array($frontend) ? $frontend : [];

        return [
            'manager_navigation' => is_array($frontend['manager_navigation'] ?? null) ? $frontend['manager_navigation'] : [],
            'manager_with_club_navigation' => is_array($frontend['manager_with_club_navigation'] ?? null) ? $frontend['manager_with_club_navigation'] : [],
            'manager_without_club_navigation' => is_array($frontend['manager_without_club_navigation'] ?? null) ? $frontend['manager_without_club_navigation'] : [],
            'admin_navigation' => is_array($frontend['admin_navigation'] ?? null) ? $frontend['admin_navigation'] : [],
            'dashboard_widgets' => array_values(is_array($frontend['dashboard_widgets'] ?? null) ? $frontend['dashboard_widgets'] : []),
            'settings_sections' => array_values(is_array($frontend['settings_sections'] ?? null) ? $frontend['settings_sections'] : []),
            'player_actions' => array_values(is_array($frontend['player_actions'] ?? null) ? $frontend['player_actions'] : []),
            'matchcenter_panels' => array_values(is_array($frontend['matchcenter_panels'] ?? null) ? $frontend['matchcenter_panels'] : []),
            'notifications' => array_values(is_array($frontend['notifications'] ?? null) ? $frontend['notifications'] : []),
        ];
    }
}
