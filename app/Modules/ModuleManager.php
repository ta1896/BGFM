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

        foreach ($this->enabledModules() as $module) {
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
                    $provider->loadMigrationsFrom($module['migration_path']);
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
        $adminNavigation = [];
        $dashboardWidgets = [];

        foreach ($this->enabledModules() as $module) {
            $frontend = $module['frontend'];
            $managerNavigation = array_merge($managerNavigation, $frontend['manager_navigation'] ?? []);
            $adminNavigation = array_merge($adminNavigation, $frontend['admin_navigation'] ?? []);
            $dashboardWidgets = array_merge($dashboardWidgets, $frontend['dashboard_widgets'] ?? []);
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
            'admin_navigation' => $adminNavigation,
            'dashboard_widgets' => $dashboardWidgets,
        ];
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
                    'frontend' => is_array($decoded['frontend'] ?? null) ? $decoded['frontend'] : [],
                    'module_path' => $modulePath,
                    'route_path' => isset($decoded['routes']) ? $modulePath.DIRECTORY_SEPARATOR.(string) $decoded['routes'] : null,
                    'migration_path' => isset($decoded['migrations']) ? $modulePath.DIRECTORY_SEPARATOR.(string) $decoded['migrations'] : null,
                ]);
            }
        }

        return $this->discovered = $modules->sortBy('name')->values();
    }

    public function enabledModules(): Collection
    {
        $stateMap = $this->moduleStateMap();

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
}
