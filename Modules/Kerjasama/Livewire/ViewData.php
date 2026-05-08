<?php

namespace Modules\Kerjasama\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Kerjasama\Helpers\Menu;

trait ViewData
{
    protected function defineViewData()
    {
        $this->viewData = [
            'resourceId' => Page::showURLInfo('id'),
            'subResourceId' => Page::showURLInfo('sub_resource_id')
        ];

        return Page::buildViewData('kerjasama', Menu::navbar(), $this->defineSidebar(), $this->defineParentSidebar(), lastBreadcrumb: $this->defineLastBreadcrumb()) + parent::defineViewData();
    }

    protected function defineSidebar()
    {
        return null;
    }

    protected function defineParentSidebar()
    {
        return null;
    }
}
