<?php

namespace Modules\Core\Models\Traits;

use Modules\Log\Observers\ActivityObserver;

trait ActivityLog
{
    protected static function bootActivityLog()
    {
        static::observe(ActivityObserver::class);
    }
}
