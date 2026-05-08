<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Modules\Core\Models\Traits\ModelTrait;

class Model extends BaseModel
{
    use ModelTrait;

    protected static function newFactory()
    {
        $class = static::class;
        $class = str_replace('Models', 'Database\\factories', $class);
        $class .= 'Factory';

        return $class::new();
    }
}
