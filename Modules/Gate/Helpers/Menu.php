<?php

namespace Modules\Gate\Helpers;

class Menu
{
    /**
     * Navbar atas.
     *
     * @return array
     */
    public static function navbar()
    {
        return [
            ['path' => 'users'],
            ['path' => 'roles'],
        ];
    }
}
