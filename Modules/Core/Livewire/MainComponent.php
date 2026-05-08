<?php

namespace Modules\Core\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Modules\Core\Helpers\Page;

abstract class MainComponent extends Component
{
    public $permission;
    public $urlInfo;
    public $viewData;

    protected $alert;
    protected $service;

    public function mount()
    {
        $this->loadService();

        $this->permission = request()->permission;
        $this->urlInfo = Page::showURLInfo();
        $this->viewData = $this->defineViewData();
    }

    public function boot()
    {
        $this->loadService();
    }

    protected function buildView($page)
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
        ]);

        return view($page);
    }

    protected function defineViewData()
    {
        return [];
    }

    protected function defineLastBreadcrumb()
    {
        return [];
    }

    protected abstract function loadService();


    protected function validatePermission(...$permission)
    {
        $isValidated = false;

        foreach ($permission as $perm) {
            if ($this->permission[$perm]) {
                $isValidated = true;
            }
        }

        return $isValidated;
    }
}
