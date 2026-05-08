<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Modules\Gate\Models\ModulCache;
use Modules\Gate\Services\AuthorizationService;

class Page
{
    use PageRoute;

    /**
     * Membuat data untuk view composer.
     *
     * @param string $module
     * @param array $menu
     * @param array $sidebar
     * @param array $parentSidebar
     * @param bool $checkPermission
     * @return array
     */
    public static function buildViewData($module, $menu, $sidebar = [], $parentSidebar = [], $checkPermission = true, $navTab = [], $lastBreadcrumb = [])
    {
        // modul
        $apakahAktifModul = ModulCache::get()[$module]['apakah_aktif'] ?? false;
        if (!$apakahAktifModul) {
            abort(404);
        }

        // menu
        $menu = static::defineMenu($module, $menu, $sidebar['parent'] ?? $navTab['parent'] ?? null, $checkPermission, $lastBreadcrumb);
        $data = [
            'menu' => $menu,
            'resourceTitle' => static::defineTitleByResource(),
            'resourceId' => static::showURLInfo('id'),
        ];
        // sidebar
        if (!empty($sidebar)) {
            $sidebar = static::defineMenu($module, $sidebar['items']);
            $data['submenu'] = $sidebar;
        }
        
        // nav tab
        if (!empty($navTab)) {
            $navTab = static::defineMenu($module, $navTab['items'], $navTab['activePath'] ?? null, checkPermission: false);
            $data['navTab'] = $navTab;
        }

        // breadcrumb
        $breadcrumb = static::showActiveMenu($menu);

        if (!empty($parentSidebar) || !empty($lastBreadcrumb['items'])) {
            $parentSidebar = static::defineMenu($module, $parentSidebar['items'] ?? $lastBreadcrumb['items']);
            $breadcrumbAdd = static::showActiveMenu($parentSidebar);
        }
        if (!empty($breadcrumbAdd)) {
            $breadcrumb = array_merge($breadcrumb, array_slice($breadcrumbAdd, -1));
            $breadcrumbAdd = null; // dicek di bawah
        }

        if (!empty($sidebar)) { // utamakan sidebar utk breadcrumb
            $breadcrumbAdd = static::showActiveMenu($sidebar);
        } elseif (!empty($navTab)) {
            $breadcrumbAdd = static::showActiveMenu($navTab);
        }

        if (!empty($breadcrumbAdd)) {
            $breadcrumb = array_merge($breadcrumb, array_slice($breadcrumbAdd, -1));
        }

        $lastBreadcrumb = count($breadcrumb) - 1;
        foreach ($breadcrumb as $i => $item) {
            $showLink = !empty($item['path']);

            if ($showLink && $i == $lastBreadcrumb) {
                $showLink = !static::isActivePath($item['path'], true);
            }

            $breadcrumb[$i]['showLink'] = $showLink;

            // penambahan pengecekan, jika sidebar / breadcrumb adalah tombol back
            // maka unset dari array breadcrumb
            if (!empty($item['isBackButton']) && $item['isBackButton']) {
                unset($breadcrumb[$i]);
            }
        }

        $data['breadcrumb'] = [
            'home' => static::homeURL(),
            'items' => $breadcrumb,
            'showTitle' => !empty($showLink),
        ];

        return $data;
    }

    /**
     * Melengkapi array menu.
     *
     * @param string $module
     * @param array $menu
     * @param string $activePath
     * @param bool $checkPermission
     * @return array
     */
    public static function defineMenu($module, $menu, $activePath = null, $checkPermission = true, $lastBreadcrumb = [])
    {
        if ($checkPermission) {
            $permissions = AuthorizationService::showRolePermissions();
        }

        if ($checkPermission && Error::isError($permissions)) {
            return [];
        }

        // jika permission empty, maka set true
        $permissions ??= true;

        // cek otorisasi dan active path
        [$menu, $activeLength] = static::defineMenuItems($module, $permissions, $menu, $activePath, $lastBreadcrumb);
        if (!empty($activeLength)) {
            $menu = static::defineActiveMenuItems($activeLength, $menu);
        }
        return $menu;
    }

    /**
     * Mendapatkan dynamic component fields.
     *
     * @param array $param
     * @param array $urlInfo
     * @return string
     */
    public static function defineFieldComponent($param, $urlInfo = null)
    {
        $component = $param['component'] ?? null;
        if (empty($component)) {
            return null;
        }

        if ($component === true) {
            $component = $param['field'];
        }

        return static::defineDynamicComponent('fields', $component, $urlInfo['module'] ?? null);
    }

    /**
     * Mendapatkan dynamic component table action.
     *
     * @param array $param
     * @param array $urlInfo
     * @return string
     */
    public static function defineTableActionComponent($param, $urlInfo = null)
    {
        $component = $param['component'] ?? null;
        if (empty($component)) {
            return null;
        }

        if ($component === true) {
            $component = $param['field'];
        }

        return static::defineDynamicComponent('table-actions', $component, $urlInfo['module'] ?? null);
    }

    /**
     * Mendapatkan dynamic component options.
     *
     * @param mixed $options hanya untuk class model
     * @return string
     */
    public static function defineOptionComponent($options)
    {
        if (empty($options) || is_array($options)) {
            return null;
        }

        [, $module,, $model] = explode('\\', $options);

        $module = strtolower($module);
        $model = Str::snake($model);

        return static::defineDynamicComponent('options', $model, $module);
    }

    /**
     * Mendapatkan title dari resource.
     *
     * @param string $resource
     * @param array $urlInfo
     * @return string
     */
    public static function defineTitleByResource($resource = null, $urlInfo = null)
    {
        if (empty($resource)) {
            $resource = empty($urlInfo) ? static::showURLInfo('lang') : $urlInfo['lang'];
        }

        return static::translateResource($resource, module: $urlInfo['module'] ?? null);
    }

    /**
     * Mendapatkan label dari field resource.
     *
     * @param string $field
     * @param array $urlInfo
     * @return string
     */
    public static function defineLabelByField($field, $urlInfo = null)
    {
        $info = $urlInfo ?? static::showURLInfo();

        return static::translateResource($info['lang'], $field, $info['module']);
    }

    /**
     * Terjemahkan field atau nama resource.
     *
     * @param string $resource
     * @param string $field
     * @param string $module
     * @return mixed
     */
    public static function translateResource($resource = null, $field = null, $module = null)
    {
        if (empty($resource) || empty($module)) {
            $info = static::showURLInfo();
        }
        if (empty($resource)) {
            $resource = $info['resource'];
        }
        if (empty($module)) {
            $module = $info['module'];
        }

        $resource = str_replace('-', '_', $resource);
        $key = $module . '::' . $resource;

        // array
        if ($field === false) {
            return Lang::has($key) ? __($key) : [];
        }

        // string
        $key .= '.' . ($field ?? 'main');

        if (Lang::has($key)) {
            return __($key);
        }

        return Str::headline($field ?? $resource);
    }

    /**
     * Mendapatkan menu aktif.
     *
     * @param array $menu
     * @return array
     */
    public static function showActiveMenu($menu)
    {
        $active = [];
        foreach ($menu as $item) {
            if (empty($item['active'])) {
                continue;
            }

            $active[] = $item;
            if (!empty($item['items'])) {
                foreach ($item['items'] as $sub) {
                    if (!empty($sub['active'])) {
                        $active[] = $sub;
                        break;
                    }
                }
            }
        }

        return $active;
    }

    /**
     * Apakah path aktif.
     *
     * @param string $path
     * @param bool $strict
     * @return mixed
     */
    public static function isActivePath($path, $strict = false)
    {
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        $active = Request::is($path) || (empty($strict) && Request::is($path . '/*'));
        if (empty($active)) {
            return false;
        }

        // return panjang path, bukan true
        return strlen($path);
    }

    /**
     * Mendapatkan dynamic component.
     *
     * @param string $folder
     * @param string $file
     * @param string $module
     * @return string
     */
    public static function defineDynamicComponent($folder, $file, $module = null)
    {
        if (empty($module)) {
            $module = static::showURLInfo('module');
        }

        $dynamicComponent = $module . '::' . $folder . '.' . $file;

        if (!View::exists($module . '::components.' . $folder . '.' . $file)) {
            return null;
        }

        return $dynamicComponent;
    }

    /**
     * Membuat blade component attributes.
     *
     * @param array $data
     * @param \Illuminate\View\ComponentAttributeBag $attributes
     * @param bool $isLivewire
     * @return \Illuminate\View\ComponentAttributeBag
     */
    public static function buildAttributes($data = null, $attributes = null, $isLivewire = null)
    {
        if (empty($attributes)) {
            $attributes = new ComponentAttributeBag();
        }

        if (!empty($data)) {
            $attributes->setAttributes($data);
        }

        if ($isLivewire === false) {
            return $attributes->whereDoesntStartWith('wire:');
        }

        return $attributes;
    }

    /**
     * URL untuk assets Quantum.
     *
     * @param string $path
     * @return string
     */
    public static function quantumAsset($path)
    {
        return asset('quantum/assets/' . $path);
    }

    /**
     * URL untuk assets Quantum 3.
     *
     * @param string $path
     * @return string
     */
    public static function quantum3Asset($path)
    {
        return asset('quantum-3/' . $path);
    }

    /**
     * Apakah path bisa diakses.
     *
     * @param string $path
     * @param array $permissions
     * @param mixed $customPermission
     * @return bool
     */
    private static function isAuthorizedPath($path, $permissions, $customPermission = null)
    {
        if ($permissions === true) {
            return true;
        }

        if ($customPermission === true) {
            return !empty($permissions);
        }

        if (!empty($customPermission)) {
            $path = $customPermission;
        } elseif ($path[0] == '/') {
            $path = substr($path, 1);
        }

        $segments = explode('/', $path, 2);
        $resource = $segments[0];
        $custom = $segments[1] ?? null;

        if (empty($custom)) {
            return !empty($permissions[$resource]['_'][0]);
        }

        return !empty($permissions[$resource][$custom]);
    }

    /**
     * Melengkapi item array menu.
     *
     * @param string $module
     * @param array $permissions
     * @param array $menu
     * @param string $activePath
     * @return array
     */
    private static function defineMenuItems($module, $permissions, &$menu, $activePath = null, $lastBreadcrumb = [])
    {
        $maxActiveLength = null;
        foreach ($menu as $i => $item) {
            if (!empty($item['path']) && !empty($lastBreadcrumb['parent_path']) && ($item['path'] === $lastBreadcrumb['parent_path'])) {
                $item['items'][] = $lastBreadcrumb;
                unset($lastBreadcrumb['parent_path']);
            }

            // cek otorisasi
            $path = $item['path'] ?? null;
            $id = $item['id'] ?? null;
            if ($path && !static::isAuthorizedPath($path, $permissions, $item['permission'] ?? null)) {
                unset($menu[$i]);
                continue;
            }

            // cek active path
            if ($path) {
                $item['path'] = $module . '/' . $path . ($id ? '/' . $id : '');
            }
            if ($path && empty($item['label'])) {
                $item['label'] = static::translateResource($path);
            }
            if ($path && empty($activePath)) {
                $item['active'] = static::isActivePath($item['path']);
            }
            if ($path && !empty($activePath)) {
                $item['active'] = ($path == $activePath) ? strlen($path) : false;
            }
            if (!empty($item['active']) && $item['active'] > $maxActiveLength) {
                $maxActiveLength = $item['active'];
            }

            // rekursi items
            $activeLength = null;
            if (!empty($item['items'])) {
                [$item['items'], $activeLength] = static::defineMenuItems($module, $permissions, $item['items'], $activePath, $lastBreadcrumb);
            }
            if (isset($item['items']) && empty($item['items']) && empty($item['separator'])) {
                unset($item['items']);
            }
            if (!empty($activeLength)) {
                $item['active'] = $activeLength;
            }
            if (!empty($activeLength) && $activeLength > $maxActiveLength) {
                $maxActiveLength = $activeLength;
            }

            $menu[$i] = $item;
            if (empty($menu[$i]['items']) && empty($menu[$i]['path']) && empty($menu[$i]['separator'])) {
                unset($menu[$i]);
            }

            // handle separator, jika semua items isinya adalah separator, maka unset
            if (!empty($menu[$i]['items'])) {
                $nItems = count($menu[$i]['items']);
                $nSeparator = 0;
                foreach ($menu[$i]['items'] as $sub) {
                    if (!empty($sub['separator'])) {
                        $nSeparator++;
                    }
                }

                if ($nItems === $nSeparator) {
                    unset($menu[$i]);
                }
            }
        }

        return [$menu, $maxActiveLength];
    }

    /**
     * Memutuskan menu yang sedang aktif.
     *
     * @param int $activeLength
     * @param array $menu
     * @return array
     */
    private static function defineActiveMenuItems($activeLength, $menu)
    {
        foreach ($menu as $i => $item) {
            if (empty($item['active']) || $item['active'] < $activeLength) {
                unset($item['active']);
            }

            if (!empty($item['items'])) {
                $item['items'] = static::defineActiveMenuItems($activeLength, $item['items']);
            }

            $menu[$i] = $item;
        }

        return $menu;
    }

    /**
     * Cek apakah punya name sync
     *
     * @bool
     */
    public static function hasSync($name)
    {
        return method_exists(static::class, 'sync' . $name);
    }
}
