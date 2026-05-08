<?php

namespace Modules\Gate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Page;
use Modules\Gate\Helpers\Menu;

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
                Page::buildViewData('gate', Menu::navbar(), $viewData['sidebar'] ?? null, $viewData['parentSidebar'] ?? null)
            );
        });

        return $next($request);
    }
}
