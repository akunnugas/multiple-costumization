<?php

namespace Modules\Log\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Log\Models\Activity;
use Modules\Log\Models\TableCache;

class ActivityLogService
{
    const ACTIVITY_INSERT = 'I';
    const ACTIVITY_UPDATE = 'U';
    const ACTIVITY_DELETE = 'D';
    const ACTIVITY_FORCE_DELETE = 'F';
    const ACTIVITY_RESTORE = 'R';

    /**
     * Log aktivitas data.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $activity
     */
    public static function log(Model $model, string $activity)
    {
        // tidak untuk tabel shared
        if ($model->getConnectionName() == 'shared') {
            return;
        }

        // hanya tabel yang dibutuhkan
        $table = $model->getTable();
        $logTable = TableCache::find($table);

        if (empty($logTable)) {
            return;
        }

        $old = $model->getOriginal();
        $new = $model->toArray();

        Activity::create([
            'table_id' => $logTable['id'],
            'record_id' => $new['id'] ?? $old['id'],
            'activity' => $activity,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
        ]);
    }
}
