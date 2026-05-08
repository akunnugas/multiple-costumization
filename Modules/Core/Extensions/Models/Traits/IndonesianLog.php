<?php

namespace Modules\Core\Extensions\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait IndonesianLog
{
    protected static function bootIndonesianLog()
    {
        static::creating(function ($model) {
            if ($model->usesTimestamps() && !empty($model->getCreatedAtColumn())) {
                $model->dibuat_oleh = Auth::id();
            }
        });

        static::updating(function ($model) {
            if ($model->usesTimestamps() && !empty($model->getUpdatedAtColumn())) {
                $model->diubah_oleh = Auth::id();
            }
        });

        if (method_exists(static::class, 'softDeleted')) {
            static::softDeleted(function ($model) {
                $model->dihapus_oleh = Auth::id();
                $model->saveQuietly();
            });
        }
    }
}
