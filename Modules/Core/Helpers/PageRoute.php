<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait PageRoute
{
    /**
     * URL untuk kembali, tidak melihat route name.
     *
     * @return string
     */
    public static function backURL()
    {
        $segments = static::showURLInfo('segments');

        $n = count($segments);
        $segments = array_slice($segments, 0, $n - ($n % 2 ? 1 : 2));

        return url(implode('/', $segments));
    }

    /**
     * URL untuk index.
     *
     * @return string
     */
    public static function indexURL()
    {
        return static::buildResourceURL('index');
    }

    /**
     * URL untuk create.
     *
     * @return string
     */
    public static function createURL()
    {
        return static::buildResourceURL('create');
    }

    /**
     * URL untuk detail.
     *
     * @param string $id
     * @param array $urlInfo
     * @return string
     */
    public static function detailURL($id, $urlInfo = null)
    {
        $url = static::buildResourceURL('show', $id, $urlInfo);
        if (empty($url)) {
            $url = static::buildResourceURL('update', $id, $urlInfo);
        }

        return $url;
    }

    /**
     * URL untuk edit.
     *
     * @param string $id
     * @return string
     */
    public static function editURL($id)
    {
        return static::buildResourceURL('edit', $id);
    }

    /**
     * URL home per modul.
     *
     * @return string
     */
    public static function homeURL()
    {
        // menggunakan route
        $routeName = static::showURLInfo('module') . '.home';
        if (Route::has($routeName)) {
            return route($routeName);
        }

        return null;
    }

    /**
     * Mendapatkan URL resource.
     *
     * @param string $type
     * @param string $id
     * @param array $urlInfo
     * @return string
     */
    public static function buildResourceURL($type, $id = null, $urlInfo = null)
    {
        // hanya berdasarkan route name
        $routeName = Route::currentRouteName();
        if (empty($routeName)) {
            return null;
        }

        $parts = explode('.', $routeName);
        $prevType = $parts[count($parts) - 1];
        $parts[count($parts) - 1] = $type;

        $toRouteName = implode('.', $parts);
        if (!Route::has($toRouteName)) {
            return null;
        }

        $urlInfo ??= static::showURLInfo();

        $routeParams = $urlInfo['parameters'];
        $segments = $urlInfo['segments'];

        $check = [];
        foreach ([$prevType, $type] as $checkType) {
            if ($checkType == 'index' || $checkType == 'create' || $checkType == 'store') {
                $check[] = 0;
            } else {
                $check[] = 1;
            }
        }

        if ($check[1] > $check[0] && !empty($id)) {
            $lastSegment = $segments[count($segments) - 1];
            $key = str_replace('-', '_', Str::singular($lastSegment));

            $routeParams[$key] = $id;
        } else if ($check[1] < $check[0]) {
            array_pop($routeParams);
        }

        return route($toRouteName, $routeParams);
    }

    /**
     * Mendapatkan URL.
     *
     * @param array $replace
     * @return string
     */
    public static function buildURL($replace = [])
    {
        $url = url()->current();
        $query = request()->getQueryString() ?? [];

        if (!empty($query)) {
            parse_str(request()->getQueryString(), $query);
        }

        foreach ($replace as $k => $v) {
            if (isset($v)) {
                $query[$k] = $v;
            } else if (!empty($query[$k])) {
                unset($query[$k]);
            }
        }

        if (!empty($query)) {
            $url .= '?' . urldecode(http_build_query($query));
        }

        return $url;
    }

    /**
     * Info dari URL
     *
     * @param null $key
     * @return array|string|null
     */
    public static function showURLInfo($key = null): array|string|null
    {
        $segments = request()->segments();
        $params = request()->route()->parameters();
        $queryParams = request()->query('filter');
        $notAllowed = ['create', 'edit', 'show', 'update', 'destroy'];

        $notAllowed = ['create', 'edit', 'show', 'update', 'destroy'];

        $lang = [];
        foreach ($segments as $i => $segment) {
            if (!empty($i % 2) && !empty($segment) && !in_array($segment, $notAllowed)) {
                $lang[] = str_replace('-', '_', $segment);
            }
        }

        $countSegments = count($segments) - 1;
        $countParams = count($params) * 2;

        if ($countSegments <= $countParams + 1) {
            $subResourceId = array_values($params)[0] ?? null;
            $id = end($params);
        } else {
            $id = null;
        }

        $info = [
            'module' => $segments[0] ?? null,
            'resource' => $segments[1] ?? null,
            'segments' => $segments,
            'parameters' => $params,
            'filter' => $queryParams,
            'lang' => implode('/', $lang),
            'id' => $id,
            'sub_resource_id' => $subResourceId ?? null
        ];

        if (empty($key)) {
            return $info;
        }

        return $info[$key] ?? null;
    }
}
