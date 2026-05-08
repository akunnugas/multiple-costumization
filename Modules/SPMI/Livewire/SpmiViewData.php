<?php

namespace Modules\SPMI\Livewire;

use Modules\Core\Helpers\Page;
use Modules\SPMI\Helpers\Menu;

trait SpmiViewData
{
    protected function defineViewData()
    {
        $this->viewData = [
            'resourceId' => Page::showURLInfo('id'),
            'subResourceId' => Page::showURLInfo('sub_resource_id')
        ];

        return Page::buildViewData('spmi', Menu::navbar(), $this->defineSidebar(), $this->defineParentSidebar(), lastBreadcrumb: $this->defineLastBreadcrumb()) + parent::defineViewData();
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
