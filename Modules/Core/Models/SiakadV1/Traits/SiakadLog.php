<?php

namespace Modules\Core\Models\SiakadV1\Traits;

use Illuminate\Support\Facades\Auth;

trait SiakadLog
{
    protected static function bootSiakadLog()
    {
        static::creating(function ($model) {
            if ($model->usesTimestamps() && !empty($model->getCreatedAtColumn())) {
                $model->softdelete = 0;
                $model->t_updateuser = Auth::id();
                $model->t_updateip = request()->ip();
            }
        });

        static::updating(function ($model) {
            if ($model->usesTimestamps() && !empty($model->getUpdatedAtColumn())) {
                $model->softdelete = 0;
                $model->t_updateuser = Auth::id();
                $model->t_updateip = request()->ip();
            }
        });
    }
}
