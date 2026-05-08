<?php

namespace Modules\SPMI\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Modules\SPMI\Livewire\ReportForm;

class SPMIServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'SPMI';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'spmi';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->register(AuthServiceProvider::class);
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerCommandSchedules();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        Livewire::component('spmi.report-form', ReportForm::class);

        $this->commands([
            \Modules\SPMI\Console\MigrateSpmeToSpmi::class,
            \Modules\SPMI\Console\SyncPeriodeAuditCommand::class,
            \Modules\SPMI\Console\MigrateRumusPenilaianCommand::class,
            \Modules\SPMI\Console\FixMatriksIktCommand::class,
            \Modules\SPMI\Console\FixDataTypeNumericPengisianIndikatorCommand::class,
            \Modules\SPMI\Console\FixDataTypeTextareaPengisianIndikatorCommand::class,
            \Modules\SPMI\Console\SyncSyaratTerakreditasiCommand::class,
        ]);

        Storage::disk('local')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            return URL::temporarySignedRoute(
                'spmi.local.temp',
                $expiration,
                array_merge($options, ['path' => str_replace('/', '|', $path)])
            );
        });
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
            $schedule->command('spmi:sync-periode-audit')
                ->name('Sync Periode Audit')
                ->runInBackground()
                ->before(function () {
                    Log::channel('scheduler')->info('SPMI - Sync audit period started at ' . now()->toDateTimeString());
                })
                ->after(function () {
                    Log::channel('scheduler')->info('Litabmas - Sync audit period finished at ' . now()->toDateTimeString());
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
