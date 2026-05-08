<?php

namespace Modules\Core\Extensions\Models\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait SevimaModel
{
    use HasFactory, ModelValidation, IndonesianLog, IndonesianRelation;

    protected static function newFactory()
    {
        $class = static::class;
        $class = str_replace('Models', 'Database\\factories', $class);
        $class .= 'Factory';

        return $class::new();
    }

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'kode asc';
    const OPTION_COLUMN = 'nama';

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
        return static::where('id', $id)->first([static::OPTION_COLUMN])?->{static::OPTION_COLUMN};
    }
}
