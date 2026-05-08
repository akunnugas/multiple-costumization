<?php

namespace Modules\PMB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Page;
use Modules\PMB\Helpers\Menu;

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
        View::composer('*::pages.*', function ($view) {
            $viewData = $view->getData();

            View::share(
                Page::buildViewData(
                    module: 'pmb',
                    menu: Menu::navbar(),
                    sidebar: $viewData['sidebar'] ?? null,
                    parentSidebar: $viewData['parentSidebar'] ?? null
                )
            );
        });

        return $next($request);
    }
}
