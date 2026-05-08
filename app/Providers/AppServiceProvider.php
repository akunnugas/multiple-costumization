<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Extensions\Schema;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Transport\NotificationMailTransport;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // agar tidak migrasi create_personal_access_tokens_table
        Sanctum::ignoreMigrations();

        // binding ke schema custom
        $this->app->bind('db.schema', fn() => SevimaSchema::connection());

        if ($this->enableDebug()) {
            debugbar()->enable();
        } else {
            debugbar()->disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('APP_ROOT_AS_PATH', false)) {
            URL::forceRootUrl(url(env('APP_ROOT_AS_PATH_FOLDER', 'v2')));
        }
        Mail::extend('notification-mail', function (array $config = []) {
            return new NotificationMailTransport($config['host'], $config['app_id'], $config['app_secret']);
        });
    }

    /**
     * Enable debug.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return boolean
     */
    protected function enableDebug(): bool
    {
        if (config('app.env') !== 'production') {
            return (bool) config('app.debug');
        }

        $whitelistedIps = explode('|', config('custom.debug.ip_addresses', ''));
        $forwarderForIp = explode(',', request()->server('HTTP_X_FORWARDED_FOR'));

        return !empty(array_intersect($forwarderForIp, $whitelistedIps));
    }
}
