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

        $this->registerTools();
    }

    protected function registerTools(): void
    {
        try {
            $registry = resolve(\Platform\Core\Tools\ToolRegistry::class);

            // Overview
            $registry->register(new \Platform\Academy\Tools\AcademyOverviewTool());

            // Topics
            $registry->register(new \Platform\Academy\Tools\ListAcademyTopicsTool());
            $registry->register(new \Platform\Academy\Tools\GetAcademyTopicTool());
            $registry->register(new \Platform\Academy\Tools\CreateAcademyTopicTool());
            $registry->register(new \Platform\Academy\Tools\UpdateAcademyTopicTool());
            $registry->register(new \Platform\Academy\Tools\DeleteAcademyTopicTool());

            // Lessons (incl. content-ops on update)
            $registry->register(new \Platform\Academy\Tools\ListAcademyLessonsTool());
            $registry->register(new \Platform\Academy\Tools\GetAcademyLessonTool());
            $registry->register(new \Platform\Academy\Tools\CreateAcademyLessonTool());
            $registry->register(new \Platform\Academy\Tools\UpdateAcademyLessonTool());
            $registry->register(new \Platform\Academy\Tools\DeleteAcademyLessonTool());

            // Paths + pivot
            $registry->register(new \Platform\Academy\Tools\ListAcademyPathsTool());
            $registry->register(new \Platform\Academy\Tools\GetAcademyPathTool());
            $registry->register(new \Platform\Academy\Tools\CreateAcademyPathTool());
            $registry->register(new \Platform\Academy\Tools\UpdateAcademyPathTool());
            $registry->register(new \Platform\Academy\Tools\DeleteAcademyPathTool());
            $registry->register(new \Platform\Academy\Tools\AttachLessonToPathTool());
            $registry->register(new \Platform\Academy\Tools\DetachLessonFromPathTool());
            $registry->register(new \Platform\Academy\Tools\ReorderPathLessonsTool());
        } catch (\Throwable $e) {
            // ToolRegistry not available yet (e.g. during migrations)
        }
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
