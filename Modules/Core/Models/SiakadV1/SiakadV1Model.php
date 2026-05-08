<?php

namespace Modules\Core\Models\SiakadV1;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\SiakadV1\Traits\SiakadLog;

class SiakadV1Model extends Model
{
    use SiakadLog;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'siakadv1';

    const CREATED_AT = 't_inserttime';
    const UPDATED_AT = 't_updatetime';
    const DELETED_AT = null;

    /**
     * Begin the transaction
     * 
     * @return void
     */
    public static function beginTransaction(): void
    {
        self::getConnectionResolver()->connection('siakadv1')->beginTransaction();
    }

    /**
     * Commit the transaction
     * 
     * @return void
     */
    public static function commit(): void
    {
        self::getConnectionResolver()->connection('siakadv1')->commit();
    }

    /**
     * Rollback the transaction
     * 
     * @return void
     */
    public static function rollBack(): void
    {
        self::getConnectionResolver()->connection('siakadv1')->rollBack();
    }
}
