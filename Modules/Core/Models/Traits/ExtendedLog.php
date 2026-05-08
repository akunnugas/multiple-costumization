<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait ExtendedLog
{
    protected static function bootExtendedLog()
    {
        static::creating(function ($model) {
            if ($model->usesTimestamps() && !empty($model->getCreatedAtColumn())) {
                $model->created_by = Auth::id();
            }
        });
        static::updating(function ($model) {
            if ($model->usesTimestamps() && !empty($model->getUpdatedAtColumn())) {
                $model->updated_by = Auth::id();
            }
        });

        if (method_exists(static::class, 'softDeleted')) {
            static::softDeleted(function ($model) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            });
        }
    }
}
