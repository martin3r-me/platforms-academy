<?php

namespace Platform\Academy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
use Platform\Core\Routing\ModuleRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AcademyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/academy.php', 'academy');
    }

    public function boot(): void
    {
        if (
            config()->has('academy.routing') &&
            config()->has('academy.navigation') &&
            Schema::hasTable('modules')
        ) {
            PlatformCore::registerModule([
                'key'        => 'academy',
                'title'      => 'Academy',
                'routing'    => config('academy.routing'),
                'guard'      => config('academy.guard'),
                'navigation' => config('academy.navigation'),
                'sidebar'    => config('academy.sidebar'),
            ]);
        }

        if (PlatformCore::getModule('academy')) {
            ModuleRouter::group('academy', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/academy.php' => config_path('academy.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'academy');

        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Academy\\Livewire';
        $prefix = 'academy';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            $aliasPath = str_replace(['\\', '/'], '.', Str::kebab(str_replace('.php', '', $relativePath)));
            $alias = $prefix . '.' . $aliasPath;

            Livewire::component($alias, $class);
        }
    }
}
