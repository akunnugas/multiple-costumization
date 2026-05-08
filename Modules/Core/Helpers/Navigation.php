<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Gate\Services\AuthorizationService;
use Nwidart\Modules\Facades\Module;

class Navigation
{
    public $module;
    public $resource;
    public $resourceId;
    public $parentResource;
    public $parentResourceId;
    public string $lastBreadcrumbName = '';

    private $currentUrl;
    private $namespace;
    private $permissions;

    private $navbar;
    private $breadcrumb;
    private $sidebar = [];
    private $sidebarParent = [null, null, []];
    private $sidebarAssigned = false;

    private static Navigation $instance;

    const HOME = 'home';

    public function __construct(string $currentUrl = null)
    {
        $this->currentUrl = $currentUrl ?? static::showCurrentUrl();

        $info = static::showPageInfo($this->currentUrl);

        $this->module = $info['module'];
        $this->resource = $info['resource'];
        $this->resourceId = $info['resource_id'] ?? null;
        $this->parentResource = $info['parent_resource'] ?? null;
        $this->parentResourceId = $info['parent_resource_id'] ?? null;

        $module = Module::find($this->module);
        if ($module) {
            $this->namespace = '\Modules\\' . $module->getStudlyName() . '\Helpers\Menu';
        }
    }


    public function showNavbar($requestPath = null): array
    {
        if (!empty($this->namespace) && !isset($this->navbar)) {
            $navbar = config($this->module . '.menu.' . Auth::user()->kode_role) ?? 'navbar';
            $this->navbar = $this->defineMenu($this->namespace::$navbar(), $requestPath);
        }

        return $this->navbar;
    }

    public function showBreadcrumb(): array
    {
        if (!isset($this->breadcrumb)) {
            $this->defineBreadcrumb();
        }

        return $this->breadcrumb;
    }

    public function getSidebar()
    {
        return $this->sidebar;
    }

    public function appendSidebar(array $item)
    {
        $this->sidebar[] = $item;
    }

    public function assignSidebar(string $class, string $method, array $params = [])
    {
        $sidebar = ($class . '::' . $method)(...$params);

        $this->sidebarAssigned = true;
        $this->sidebar = array_merge($this->sidebar, $this->defineMenu($sidebar['items']));
        $this->sidebarParent = [$class, $sidebar['parent'], $params];
    }

    public function assignNestedBreadcrumb(string $class, string $parent, array $params = [])
    {
        $this->sidebarParent = [$class, $parent, $params];
    }

    private function defineMenu(array $items, string $activePath = null): array
    {
        $service = new AuthorizationService;
        $this->permissions = $service->showRolePermissions();

        if (Error::isError($this->permissions)) {
            return [];
        }

        [$items, $activeLength] = $this->defineMenuItems($items, $activePath);
        $items = $this->defineActiveMenuItems($items, $activeLength);

        return $items;
    }

    private function defineMenuItems(array $items, string $activePath = null): array
    {
        $maxActiveLength = 0;
        foreach ($items as $i => $item) {
            $item = $this->defineMenuItemPath($item);
            if ($item === false) {
                unset($items[$i]);
                continue;
            }

            // rekursi items
            $activeLength = 0;
            if (!empty($item['items'])) {
                [$item['items'], $activeLength] = $this->defineMenuItems($item['items'], $activePath);

                if (empty($item['items'])) {
                    unset($items[$i]);
                    continue;
                }
            }

            // aktif
            if (empty($activeLength) && !empty($item['path'])) {
                $activeLength = $this->isActivePath($item['path'], $activePath);
            }
            if (!empty($activeLength) && $activeLength > $maxActiveLength) {
                $maxActiveLength = $activeLength;
            }

            $item['active'] = $activeLength;

            $items[$i] = $item;
        }

        return [$items, $maxActiveLength];
    }

    private function defineMenuItemPath(array $item)
    {
        $path = $item['path'] ?? null;
        if (empty($path)) {
            return $item;
        }

        if ($path[0] == '/') {
            $path = substr($path, 1);
        }

        // otorisasi
        if (!$this->isAuthorizedPath($path, $item['permission'] ?? null)) {
            return false;
        }

        $item['path'] = $this->module . ($path ? '/' . $path : '');

        // label
        if (empty($item['label'])) {
            $item['label'] = $this->translatePath($path);
        }

        return $item;
    }

    private function defineActiveMenuItems(array $items, int $activeLength): array
    {
        if (empty($activeLength)) {
            return $items;
        }

        return $this->iterateMenu($items, function ($item) use ($activeLength) {
            if (isset($item['active']) && $item['active'] < $activeLength) {
                unset($item['active']);
            }

            return $item;
        });
    }

    private function defineBreadcrumb()
    {
        $this->breadcrumb = [];

        // cek parent sidebar
        [$class, $parent, $params] = $this->sidebarParent;

        $sidebarParent = [];
        while (!empty($parent) && str_contains($parent, '.')) {
            [$method, $path] = explode('.', $parent);
            $sidebar = ($class . '::' . $method)(...$params);

            // items
            $items = $this->defineMenu($sidebar['items'], $path);
            $this->iterateMenu($items, function ($item) use (&$sidebarParent) {
                $item = $this->defineBreadcrumbItemSidebar($item, true);
                if (!empty($item)) {
                    array_unshift($sidebarParent, $item);
                }
            });

            // parent
            $parent = $sidebar['parent'];
        }

        // buat breadcrumb
        $this->iterateMenu($this->showNavbar($parent ?? null), [$this, 'defineBreadcrumbItem']);
        $this->breadcrumb = array_merge($this->breadcrumb, $sidebarParent);
        $this->iterateMenu($this->sidebar, [$this, 'defineBreadcrumbItemSidebar']);

        // set last breadcrumb
        if (!empty($this->lastBreadcrumbName)) {
            $this->breadcrumb[] = ['label' => $this->lastBreadcrumbName];
        } else if (isset($this->resourceId)) {
            if ($this->resourceId === 'create') {
                $this->breadcrumb[] = ['label' => 'Tambah'];
            } else if (!$this->sidebarAssigned) {
                $this->breadcrumb[] = ['label' => 'Detail'];
            }
        }

        // asumsi item terakhir adalah halaman sekarang
        if (!empty($this->breadcrumb)) {
            unset($this->breadcrumb[count($this->breadcrumb) - 1]['path']);
        }
    }

    private function defineBreadcrumbItem($item, $returnItem = false, $isSidebar = false)
    {
        if (empty($item['active']) || ($isSidebar && !empty($item['items']))) {
            return;
        }

        $activeItem = Arr::only($item, ['path', 'label']);
        if (!empty($returnItem)) {
            return $activeItem;
        }

        $this->breadcrumb[] = $activeItem;
    }

    private function defineBreadcrumbItemSidebar($item, $returnItem = false)
    {
        return $this->defineBreadcrumbItem($item, $returnItem, true);
    }

    private function iterateMenu(array $items, callable $callback): array
    {
        if (empty($items)) {
            return $items;
        }

        foreach ($items as $i => $item) {
            $items[$i] = $callback($item) ?? $item;

            if (!empty($item['items'])) {
                $items[$i]['items'] = $this->iterateMenu($item['items'], $callback);
            }
        }

        return $items;
    }

    private function isAuthorizedPath(string $path, bool $permission = null): bool
    {
        if ($permission === true) {
            return !empty($this->permissions);
        }

        [$resource] = explode('/', $path);
        if (empty($this->permissions[$resource])) {
            return false;
        }

        return ($this->permissions[$resource] === true || !empty($this->permissions[$resource]['_'][0]));
    }

    private function translatePath(string $path): string
    {
        $resource = ($path == '' ? static::HOME : str_replace('-', '_', $path));
        $key = $this->module . '::' . $resource . '.main';

        if (Lang::has($key)) {
            return __($key);
        }

        return Str::headline($resource);
    }

    private function isActivePath(string $path, string $activePath = null): int
    {
        if (empty($activePath)) {
            $activePath = static::showActivePath($this->currentUrl);
        } else {
            $activePath = $this->module . '/' . $activePath;
        }

        if ($path == $activePath || Str::startsWith($activePath, $path . '/')) {
            return strlen($path);
        }

        return false;
    }

    // static functions

    public static function getInstance(string $currentUrl = null): Navigation
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($currentUrl);
        }

        return self::$instance;
    }

    public static function showPageInfo(string $url = null, string $type = null)
    {
        $parts = static::showURLParts($url);

        // resource saat ini
        $currentResource = $parts[1] ?? static::HOME;
        $currentResourceId = (!empty($parts[2])) ? $parts[2] : null;

        // cek apakah ada parent resource
        $hasParent = false;
        $parentResource = null;
        $parentResourceId = null;
        if (isset($currentResourceId) && isset($parts[3])) {
            $hasParent = true;
            $parentResource = $currentResource;
            $parentResourceId = $currentResourceId;
            $currentResource = config('akademik.resource.' . $currentResource . ':' . $parts[3]) ?? $parts[3];
            $currentResourceId = (!empty($parts[4])) ? $parts[4] : null;
        }

        $parts = [
            'module' => $parts[0],
            'resource' => $currentResource,
            'resource_id' => $currentResourceId,
            'parent_resource' => $hasParent ? $parentResource : null,
            'parent_resource_id' => $hasParent ? $parentResourceId : null,
        ];

        if (empty($type)) {
            return $parts;
        }

        return $parts[$type];
    }

    public static function showModuleHome(string $module = null): string
    {
        if (empty($module)) {
            $module = static::showPageInfo(type: 'module');
        }

        $routeName = $module . '.home';
        if (Route::has($routeName)) {
            return route($routeName);
        }

        return url('/' . $module);
    }

    public static function pullIntended()
    {
        $url = session()->pull('url.intended');
        if (empty($url)) {
            return $url;
        }

        $root = url()->to('/');
        if (Str::startsWith($url, $root)) {
            return $url;
        }

        return $root . Str::after($url, request()->root());
    }

    public static function showCurrentUrl()
    {
        return url()->current();
    }

    public static function validateId($id)
    {
        if (is_array($id)) {
            $id = array_filter($id, function ($v) {
                return filter_var($v, FILTER_VALIDATE_INT);
            });

            return !empty($id);
        }

        return filter_var($id, FILTER_VALIDATE_INT);
    }

    public static function isInvalidId($id, array $customAllowedParamIds = null): bool
    {
        return isset($id) && !static::validateId($id) && $id !== 'create' && !in_array($id, $customAllowedParamIds);
    }

    private static function showActivePath(string $url = null): string
    {
        if (empty($url)) {
            $url = static::showCurrentUrl();
        }

        return substr($url, strlen(url()->to('/')) + 1);
    }

    private static function showURLParts(string $url = null): array
    {
        return explode('/', static::showActivePath($url));
    }
}
