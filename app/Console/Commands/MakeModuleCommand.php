<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'module:make {name} {--enabled}';

    protected $description = 'Create a new feature module scaffold';

    public function handle(Filesystem $files): int
    {
        $rawName = (string) $this->argument('name');
        $studlyName = Str::studly($rawName);
        $modulePath = base_path('modules/'.$studlyName);

        if ($files->exists($modulePath)) {
            $this->error('Module already exists.');

            return self::FAILURE;
        }

        $files->makeDirectory($modulePath.'/resources/js', 0755, true);
        $files->makeDirectory($modulePath.'/routes', 0755, true);
        $files->makeDirectory($modulePath.'/database/migrations', 0755, true);

        $manifest = [
            'key' => Str::slug($rawName),
            'name' => $studlyName,
            'version' => '1.0.0',
            'description' => $studlyName.' feature module',
            'enabled' => (bool) $this->option('enabled'),
            'providers' => [],
            'routes' => 'routes/web.php',
            'migrations' => 'database/migrations',
            'frontend' => [
                'manager_navigation' => new \stdClass(),
                'admin_navigation' => new \stdClass(),
                'dashboard_widgets' => [],
                'settings_sections' => [],
                'player_actions' => [],
                'matchcenter_panels' => [],
                'notifications' => [],
            ],
        ];

        $files->put($modulePath.'/module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
        $files->put($modulePath.'/routes/web.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Module routes for {$studlyName}\n");
        $files->put($modulePath.'/resources/js/module.js', "export default {\n    key: '".Str::slug($rawName)."',\n};\n");
        $files->put($modulePath.'/README.md', '# '.$studlyName.PHP_EOL.PHP_EOL.'Feature module scaffold.'.PHP_EOL);

        $this->info('Module scaffold created: '.$modulePath);

        return self::SUCCESS;
    }
}
