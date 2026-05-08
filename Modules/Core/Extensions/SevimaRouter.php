<?php

namespace Modules\Core\Extensions;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;

class SevimaRouter
{
    private $path;
    private $prefix;

    /**
     * Create a new router.
     *
     * @param  string  $module
     * @return void
     */
    public function __construct(private $module)
    {
    }

    /**
     * Route for modul resource.
     *
     * @param $name
     * @param $controller
     * @param $withDestroySome
     * @param $indexComponent
     */
    public function resource($name, $controller, $withDestroySome = true, $indexComponent = null, $withSync = false)
    {
        $this->initializeByName($name);

        if ($withDestroySome && empty($indexComponent)) {
            Route::post($this->path . '/delete', [$controller, 'destroySome'])->name($this->prefix . '.destroy_some');
        }

        $names = [
            'create' => $this->prefix . '.create',
            'store' => $this->prefix . '.store',
            'show' => $this->prefix . '.show',
            'edit' => $this->prefix . '.edit',
            'update' => $this->prefix . '.update',
        ];

        if ($withSync) {
            Route::post($this->path . '/sync', [$controller, 'sync'])->name($this->prefix . '.sync');
        }

        // tanpa index livewire
        if (empty($indexComponent)) {
            $names['index'] = $this->prefix . '.index';
            $names['destroy'] = $this->prefix . '.destroy';

            return Route::resource($name, $controller)->names($names);
        }

        // menggunakan index livewire
        if (!str_starts_with($indexComponent, '\\')) {
            $indexComponent = '\\' . $indexComponent;
        }

        Route::get($this->path, $indexComponent)->name($this->prefix . '.index');

        return Route::resource($name, $controller)->except(['index', 'destroy'])->names($names);
    }

    /**
     * Route for modul reference resource.
     *
     * @param $name
     * @param $controller
     */
    public function referenceResource($name, $controller)
    {
        $isLivewire = is_a($controller, Component::class, true);

        // menggunakan controller
        if (empty($isLivewire)) {
            return $this->resource($name, $controller)->except(['create', 'show', 'edit']);
        }

        // menggunakan livewire
        if (!str_starts_with($controller, '\\')) {
            $controller = '\\' . $controller;
        }

        $this->initializeByName($name);

        Route::get($this->path, $controller)->name($this->prefix . '.index');
    }

    /**
     * Initialize attributes by name
     *
     * @param $name
     */
    private function initializeByName($name)
    {
        $resources = explode('.', $name);

        $this->path = '';
        foreach ($resources as $i => $resource) {
            $this->path .= $resource;
            if ($i == (count($resources) - 1)) {
                break;
            }

            $this->path .= '/{' . str_replace('-', '_', $resource) . '}/';
        }

        $this->prefix = $this->module . '.' . $name;
    }

    /**
     * Route untuk livewire yang tidak menggunakan resource dan namespace
     *
     * @param $path
     * @param $livewireComponent
     * @return \Illuminate\Routing\Route
     */
    public function livewireComponent($path, $livewireComponent)
    {
        // asumsi tidak menggunakan namespace
        if (!str_starts_with($livewireComponent, '\\')) {
            $livewireComponent = '\\' . $livewireComponent;
        }

        return Route::get($path, $livewireComponent);
    }
}
