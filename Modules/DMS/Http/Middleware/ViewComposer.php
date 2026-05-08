<?php

namespace Modules\DMS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Page;
use Modules\DMS\Helpers\Menu;

class ViewComposer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        View::composer(['*::pages.*', '*::livewire.*'], function () {
            $extra = [
                'maxSize' => 50, // in GB
                'currentSize' => 30, // in GB
            ];

            View::share(
                [
                    ...Page::buildViewData(
                        module: 'dms',
                        menu: Menu::navbar(),
                        sidebar: Menu::mobileSidebar(),
                        checkPermission: false,
                    ),
                    ...$extra,
                ]
            );
        });

        return $next($request);
    }
}
