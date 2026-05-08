<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\IpUtils;

class Healthz extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $allowedConfigs = env('HEALTH_CHECK_IP', null);

        $allowedIps = [];
        if (!empty($allowedConfigs)) {
            $allowedIps = explode('|', $allowedConfigs);
        }

        $ip = $request->ip();

        foreach ($allowedIps as $allowedIp) {
            if (IpUtils::checkIp($ip, $allowedIp)) {
                return $next($request);
            }
        }

        abort(403, 'Forbidden');
    }
}
