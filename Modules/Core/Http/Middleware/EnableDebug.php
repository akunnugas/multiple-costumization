<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnableDebug
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->enableDebug($request)) {
            debugbar()->enable();
        } else {
            debugbar()->disable();
        }

        return $next($request);
    }

    /**
     * Enable debug.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return boolean
     */
    public function enableDebug(Request $request)
    {
        $isProduction = config('app.env') === 'production';

        if (!$isProduction) {
            return true;
        }

        // cek alamat ip
        $whitelistedIpEnv = config('custom.debug.ip_addresses');
        $ipAddresses = explode('|', $whitelistedIpEnv);
        if (empty($ipAddresses)) {
            return false;
        }

        $userIp = $request->ip();
        $isWhitelisted = in_array($userIp, $ipAddresses);

        if ($isWhitelisted) {
            return true;
        }

        return false;
    }
}
