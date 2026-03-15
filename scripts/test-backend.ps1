$ErrorActionPreference = 'Stop'

$repoRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$sail = Join-Path $repoRoot 'vendor\bin\sail'
$composeFile = Join-Path $repoRoot 'docker-compose.yml'
$artisan = Join-Path $repoRoot 'artisan'

if (-not (Test-Path $artisan)) {
    Write-Error "artisan not found at $artisan"
}

if (Test-Path $composeFile) {
    $docker = Get-Command docker -ErrorAction SilentlyContinue
    if ($docker) {
        & docker compose exec -T app php artisan test @args
        exit $LASTEXITCODE
    }
}

if (Test-Path $sail) {
    & $sail artisan test @args
    exit $LASTEXITCODE
}

$phpCandidates = @(
    $env:PHP_BINARY,
    'C:\xampp\php\php.exe',
    'C:\laragon\bin\php\php-8.4.0-Win32-vs17-x64\php.exe',
    'C:\laragon\bin\php\php-8.3.0-Win32-vs16-x64\php.exe',
    'C:\Program Files\PHP\php.exe',
    'C:\tools\php\php.exe'
) | Where-Object { $_ }

$php = $phpCandidates | Where-Object { Test-Path $_ } | Select-Object -First 1

if (-not $php) {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCommand) {
        $php = $phpCommand.Source
    }
}

if (-not $php) {
    Write-Error "No Docker/Sail or php.exe found. Start Docker or expose PHP via PATH/PHP_BINARY, then rerun 'npm run test:backend'."
}

& $php $artisan test @args
exit $LASTEXITCODE
