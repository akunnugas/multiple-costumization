<?php

namespace Modules\Gate\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Core\Helpers\Page;

class AuthorizeRole extends AuthorizeHome
{
    /**
     * Determine if the user is authorized.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $params
     */
    protected function authorize(Request $request, array $params)
    {
        // sesuai parent
        $isAuthorized = parent::authorize($request, $params);

        if (empty($isAuthorized)) {
            return false;
        }

        // user internal
        if ($this->permission === true) {
            return true;
        }

        // ambil permission resource
        $this->permission = $this->permission[Page::showURLInfo('resource')] ?? null;

        if (empty($this->permission)) {
            return false;
        }

        // tanpa parameter
        if (empty($params)) {
            return !empty($this->definePermission()[strtolower($request->method())]);
        }

        // dengan parameter, salah satu saja boleh
        foreach ($params as $param) {
            if (!empty($this->permission[$param])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine user permission.
     */
    protected function definePermission()
    {
        if ($this->permission === true) {
            return parent::definePermission();
        }

        return [
            'get' => !empty($this->permission['_'][0]),
            'post' => !empty($this->permission['_'][1]),
            'put' => !empty($this->permission['_'][2]),
            'delete' => !empty($this->permission['_'][3]),
            'custom' => Arr::except($this->permission, '_'),
        ];
    }
}
