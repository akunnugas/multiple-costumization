<?php

namespace Modules\Core\Enums;

use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

trait ErrorEnum
{
    function code()
    {
        return Str::snake($this->name);
    }

    function message($attribute = null)
    {
        [, $module] = explode('\\', __CLASS__);
        $module = Module::find($module)->getLowerName();

        $lang = __(
            $module . '::error.' . ($this->group() ? $this->group() . '.' : '') . $this->code(),
            ['attribute' => $attribute]
        );

        return trim($lang);
    }

    function group()
    {
        return null;
    }
}
