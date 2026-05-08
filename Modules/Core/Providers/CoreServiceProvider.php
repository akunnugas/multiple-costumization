<?php

namespace Modules\Core\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Core';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'core';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerCommandSchedules();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->commands([
            \Modules\Core\Console\FactoryMakeCommand::class,
            \Modules\Core\Console\LangMakeCommand::class,
            \Modules\Core\Console\MigrateCommand::class,
            \Modules\Core\Console\ModelMakeCommand::class,
            \Modules\Core\Console\SyncKepegawaianCommand::class,
            \Modules\Core\Console\ModelTestMakeCommand::class,
            \Modules\Core\Console\ModelTestFixtureMakeCommand::class,
            \Modules\Core\Console\BasicControllerMakeCommand::class,
            \Modules\Core\Console\ServiceMakeCommand::class,
            \Modules\Core\Console\ServiceTestMakeCommand::class,
            \Modules\Core\Console\SyncUnitKerjaCommand::class,
            \Modules\Core\Console\FeatureMakeCommand::class,
            \Modules\Core\Console\SyncWilayahCommand::class,

            // migrate data
            \Modules\Core\Console\MigrateMahasiswaFromSiakadV1::class,
            \Modules\Core\Console\MigrateUniversitasFromSiakadV1::class,
            \Modules\Core\Console\MigrateRoleCommand::class
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // wilayah (negara, provinsi, kota/kabupaten, kecamatan)
            // $schedule->command('app:sync-wilayah')
            //     ->name('Sync Data Wilayah')
            //     ->runInBackground()
            //     ->before(function () {
            //         Log::channel('scheduler')->info('Core - Sync data wilayah started at ' . now()->toDateTimeString());
            //     })
            //     ->after(function () {
            //         Log::channel('scheduler')->info('Core - Sync data wilayah finished at ' . now()->toDateTimeString());
            //     })
            //     ->dailyAt('01:00');


            $schedule->command('app:sync-kepegawaian')
                ->name('Sync Data Kepegawaian')
                ->runInBackground()
                ->before(function () {
                    Log::channel('scheduler')->info('Core - Sync data pegawai started at ' . now()->toDateTimeString());
                })
                ->after(function () {
                    Log::channel('scheduler')->info('Core - Sync data pegawai finished at ' . now()->toDateTimeString());
                })
                ->dailyAt('01:00');

            $schedule->command('app:sync-unit-kerja')
                ->name('Sync Data Unit Kerja')
                ->runInBackground()
                ->before(function () {
                    Log::channel('scheduler')->info('Core - Sync data unit kerja started at ' . now()->toDateTimeString());
                })
                ->after(function () {
                    Log::channel('scheduler')->info('Core - Sync data unit kerja finished at ' . now()->toDateTimeString());
                })
                ->dailyAt('01:00');
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
