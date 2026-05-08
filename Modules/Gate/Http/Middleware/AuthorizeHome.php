<?php

namespace Modules\Gate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Helpers\Error;
use Modules\Gate\Services\AuthorizationService;

class AuthorizeHome
{
    protected $permission;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$params
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$params)
    {
        if (!$this->authorize($request, $params)) {
            return redirect()->route('login');
        }

        $request->merge(['permission' => $this->definePermission()]);

        return $next($request);
    }

    /**
     * Determine if the user is authorized.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $params
     */
    protected function authorize(Request $request, array $params)
    {
        $this->permission = AuthorizationService::showRolePermissions();

        return !empty(Error::returnValue($this->permission));
    }

    /**
     * Determine user permission.
     */
    protected function definePermission()
    {
        return [
            'get' => true,
            'post' => true,
            'put' => true,
            'delete' => true,
            'custom' => true,
        ];
    }
}
