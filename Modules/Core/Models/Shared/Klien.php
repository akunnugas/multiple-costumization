<?php

namespace Modules\Core\Models\Shared;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\ClearCache;

class Klien extends IndonesianModel
{
    use ClearCache, SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'shared';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'klien';

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        KlienCache::destroyByClient($model);
    }
}
