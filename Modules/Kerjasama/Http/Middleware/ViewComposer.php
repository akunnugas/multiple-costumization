<?php

namespace Modules\Kerjasama\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Page;
use Modules\Kerjasama\Helpers\Menu;

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
            $viewData = $view->getData();

            View::share(
                Page::buildViewData(
                    module: 'kerjasama',
                    menu: Menu::navbar(),
                    sidebar: $viewData['sidebar'] ?? null,
                    parentSidebar: $viewData['parentSidebar'] ?? null,
                    navTab: $viewData['navTab'] ?? null,
                    lastBreadcrumb: $viewData['lastBreadcrumb'] ?? [],
                )
            );
        });

        return $next($request);
    }
}
