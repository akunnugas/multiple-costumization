<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Core\Services\DomainDistributionService;
use Symfony\Component\HttpFoundation\Response;

class SwitchDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $url = url()->current();
        $pos = strpos($url, '/', 8);

        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }

        $client = DomainDistributionService::findClient($url);

        // Jika client tidak ditemukan, maka abort 404
        if (empty($client)) {
            abort(404);
        }

        // Penambahan config siakad v1 secara dinamis
        $client['url_siakad_menu'] = null;
        $client['url_siakad_callback'] = null;
        if (!empty($client['url_siakad'])) {
            $client['url_siakad_menu'] = $client['url_siakad'] . '/gate/menu';
            $client['url_siakad_callback'] = $client['url_siakad'] . '/gate/callbackv2';
        }

        // override dari data client
        if (!empty($client)) {
            $request->merge(['client' => $client]);
            DatabaseDistributionService::switchClient($client);
        }

        // sekalian set timezone
        if (!empty($client['timezone'])) {
            config(['app.timezone' => $client['timezone']]);
        }

        // TODO: sekarang sudah di-handle debugbar
        /* if (config('app.debug')) {
            DB::enableQueryLog();
            DB::connection('shared')->enableQueryLog();
        } */

        return $next($request);
    }
}
