<?php

namespace Modules\Admission\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class AuthorizeAdmission extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        Auth::setDefaultDriver('admission');

        return $next($request);
    }
}
