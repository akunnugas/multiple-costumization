<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait ModelTrait
{
    use ActivityLog, ExtendedLog, HasFactory, ModelValidation;

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'code asc';
    const OPTION_COLUMN = 'name';

    /**
     * Display options.
     * @return array
     */
    public static function options()
    {
        $orderColumn = static::OPTION_ORDER;

        return static::orderByRaw($orderColumn)
            ->get(['id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    /**
     * Display option value.
     * @param int $id
     * @return mixed
     */
    public static function optionValue($id)
    {
        return static::where('id', $id)->first([static::OPTION_COLUMN])->{static::OPTION_COLUMN};
    }
}
