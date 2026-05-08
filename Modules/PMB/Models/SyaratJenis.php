<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;
use Modules\PMB\Models\Cache\JenisSyaratCache;

class SyaratJenis extends IndonesianModel
{
    use ClearCache, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.syarat_jenis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        // 'kode_jenis_syarat', tidak bisa diubah
        'nama_jenis_syarat',
    ];

    const CODE_ADMINISTRASI = 'ADM';
    const CODE_BIDIK_MISI = 'BM';
    const CODE_DAFTAR_ULANG = 'DFU';
    const CODE_UKT = 'UKT';
    const CODES = [
        self::CODE_ADMINISTRASI,
        self::CODE_BIDIK_MISI,
        self::CODE_DAFTAR_ULANG,
        self::CODE_UKT,
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_jenis_syarat' => ['required' => true, 'maxlength' => 10, 'unique' => true], // Kode Jenis Syarat
        'nama_jenis_syarat' => ['required' => true, 'maxlength' => 255], // Nama Jenis Syarat
    ];

    private static function clearCache($model)
    {
        JenisSyaratCache::destroy();
    }
}
