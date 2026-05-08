<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReleaseModuleGuard
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
        $whitelistedModules = env('RELEASE_MODUL', null);

        if (empty($whitelistedModules)) {
            return $next($request);
        }

        $whitelistedModules = explode('|', $whitelistedModules);
        $currentModule = $request->segment(1);

        if (empty($currentModule)) {
            return $next($request);
        }

        if (in_array($currentModule, ['livewire', 'gate', 'home'])) {
            return $next($request);
        }

        if (!in_array($currentModule, $whitelistedModules)) {
            abort(404);
        }

        return $next($request);
    }
}
