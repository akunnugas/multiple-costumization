<?php

namespace Modules\Core\Extensions;

use Illuminate\Auth\GenericUser;

class SiakadUser extends GenericUser
{
    /**
     * Get all of the user's attributes.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Get the "remember me" token value.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return null;
    }
}
