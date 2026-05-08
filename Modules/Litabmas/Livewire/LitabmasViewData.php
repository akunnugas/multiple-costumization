<?php

namespace Modules\Litabmas\Livewire;

use Modules\Core\Helpers\Page;
use Modules\Litabmas\Helpers\Menu;

trait LitabmasViewData
{
    protected function defineViewData()
    {
        return Page::buildViewData(
            'litabmas',
            Menu::navbar(),
            $this->defineSidebar(),
            $this->defineParentSidebar()
        ) + parent::defineViewData();
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
