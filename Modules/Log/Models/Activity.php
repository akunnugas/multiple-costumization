<?php

namespace Modules\Log\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Activity extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log.activities';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;
}
