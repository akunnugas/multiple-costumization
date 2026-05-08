<?php

namespace Modules\Gate\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\TreeStructure;
use Modules\Core\Extensions\Models\Traits\ClearCache;

class Modul extends IndonesianModel
{
    use ClearCache, SoftDeletes, TreeStructure;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gate.modul';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_modul',
        'kode_modul',
        'apakah_aktif',
        'id_parent',
        'info_level',
        'info_left',
        'info_right',
    ];

    const CODE_ADMIN = 'gate';
    const CODE_DMS = 'dms';
    const CODE_SAMPLE = 'sample';

    public static function getQuantumVersion($modul = null)
    {
        $modulDesign = [

        ];
    }

    /**
     * Get the module's home url
     */
    protected function urlHome(): Attribute
    {
        return Attribute::make(
            get: fn () => Route::has($this->kode_modul . '.home') ? route($this->kode_modul . '.home') : url($this->kode_modul),
        );
    }

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        ModulCache::destroy();
    }
}
