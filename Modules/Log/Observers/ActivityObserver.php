<?php

namespace Modules\Log\Observers;

use Modules\Log\Services\ActivityLogService;

class ActivityObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    public function created($model)
    {
        return ActivityLogService::log($model, ActivityLogService::ACTIVITY_INSERT);
    }

    public function updated($model)
    {
        return ActivityLogService::log($model, ActivityLogService::ACTIVITY_UPDATE);
    }

    public function deleted($model)
    {
        return ActivityLogService::log($model, ActivityLogService::ACTIVITY_DELETE);
    }

    public function forceDeleted($model)
    {
        return ActivityLogService::log($model, ActivityLogService::ACTIVITY_FORCE_DELETE);
    }

    public function restored($model)
    {
        return ActivityLogService::log($model, ActivityLogService::ACTIVITY_RESTORE);
    }
}
