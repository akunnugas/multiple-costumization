<?php

namespace Modules\Gate\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Gate\Helpers\Menu;

trait GateViewData
{
    protected function defineViewData()
    {
        return Page::buildViewData('gate', Menu::navbar(), $this->defineSidebar(), $this->defineParentSidebar()) + parent::defineViewData();
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
