<?php

namespace Modules\Log\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;

class Table extends IndonesianModel
{
    use ClearCache;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log.tables';

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        TableCache::destroy($model->getOriginal('name'));
    }
}
