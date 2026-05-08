<?php

namespace Modules\Core\Models\Shared;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class KlienConfig extends IndonesianModel
{
    use SoftDeletes;

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
    protected $table = 'klien_config';

    public static function getKodeKlien()
    {
        $key = config('database.default');
        $dbName = config('database.connections.' . $key . '.database');
        $klienConfig = self::where('nama_db', $dbName)
            ->first();
        
        if (!$klienConfig) {
            throw new \Exception("Klien config not found for database: " . $dbName);
        }

        $klien = Klien::find($klienConfig->id_klien);
        if (!$klien) {
            throw new \Exception("Klien not found for ID: " . $klienConfig->id_klien);
        }

        return $klien->kode_klien;
    }

    public static function getKodeDikti()
    {
        $key = config('database.default');
        $dbName = config('database.connections.' . $key . '.database');
        $klienConfig = self::where('nama_db', $dbName)
            ->first();
        
        if (!$klienConfig) {
            throw new \Exception("Klien config not found for database: " . $dbName);
        }

        $klien = Klien::find($klienConfig->id_klien);
        if (!$klien) {
            throw new \Exception("Klien not found for ID: " . $klienConfig->id_klien);
        }

        return $klien->kode_dikti;
    }
}
