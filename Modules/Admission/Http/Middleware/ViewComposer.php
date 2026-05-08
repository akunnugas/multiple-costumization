<?php

namespace Modules\Admission\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Page;
use Modules\Admission\Helpers\Menu;

class ViewComposer
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
        View::composer(['*::pages.*', '*::livewire.*'], function ($view) {
            View::share(
                Page::buildViewData(
                    module: 'admission',
                    menu: Menu::navbar(),
                    sidebar: Menu::mobileSidebar(),
                    checkPermission: false
                )
            );
        });

        return $next($request);
    }
}
