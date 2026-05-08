<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;
use Modules\Litabmas\Database\factories\PeriodePendanaanFactory;
use Modules\Litabmas\Models\Cache\PeriodePendanaanCache;

class PeriodePendanaan extends IndonesianModel
{
    use HasFactory, SoftDeletes, ClearCache;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.periode_pendanaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tahun',
        'tanggal_mulai',
        'tanggal_akhir',
        'maksimal_ketua_mendaftar',
        'maksimal_anggota_mendaftar',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'tahun' => ['required' => true, 'maxlength' => 4, 'unique' => true], // Periode
        'tanggal_mulai' => ['required' => true, 'type' => 'date'], // Tanggal Mulai Periode
        'tanggal_akhir' => ['required' => true, 'type' => 'date', 'validation' => 'after:tanggal_mulai'], // Tanggal Akhir Periode
        'maksimal_ketua_mendaftar' => ['required' => true, 'options' => self::MAX_KETUA_MENDAFTAR], // Maksimal ketua mendaftar di satu periode pendanaan
        'maksimal_anggota_mendaftar' => ['required' => true, 'options' => self::MAX_ANGGOTA_MENDAFTAR], // Maksimal anggota mendaftar di satu periode pendanaan
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'tahun desc';
    const OPTION_COLUMN = 'tahun';

    /**
     * Constant utk maksimal ketua mendaftar dan anggota mendaftar di satu periode pendanaan.
     */
    const MAX_KETUA_MENDAFTAR = [
        1 => '1 kali',
        2 => '2 kali',
        3 => '3 kali',
        4 => '4 kali',
        5 => '5 kali',
    ];
    const MAX_ANGGOTA_MENDAFTAR = [
        1 => '1 kali',
        2 => '2 kali',
        3 => '3 kali',
        4 => '4 kali',
        5 => '5 kali',
    ];

    /**
     * @return PeriodePendanaanFactory
     */
    protected static function newFactory()
    {
        return PeriodePendanaanFactory::new();
    }

    /**
     * When model is booted, do something.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::booted();

        static::saved(function ($model) {
            self::clearCache($model);
            self::clearCacheCustom(PeriodePendanaanCache::KEY_ACTIVE_PERIOD);
        });
        static::updated(function () {
            static::clearCacheCustom(PeriodePendanaanCache::KEY_ACTIVE_PERIOD);
        });
        static::deleted(function () {
            static::clearCacheCustom(PeriodePendanaanCache::KEY_ACTIVE_PERIOD);
        });
    }

    /**
     * Get periode yang aktif (berdasarkan tanggal sekarang).
     *
     * @return mixed
     */
    public static function periodeAktif()
    {
        return PeriodePendanaanCache::periodeAktif();
    }

    /**
     * Clear/forgot cache.
     *
     * @param $model
     * @return void
     */
    private static function clearCache($model)
    {
        PeriodePendanaanCache::destroy();
    }

    /**
     * Clear/forgot custom cache.
     *
     * @param string $key
     * @return void
     */
    private static function clearCacheCustom($key)
    {
        PeriodePendanaanCache::destroyCustomKey($key);
    }

    /**
     * Relasi ke pengajuan pendanaan
     */
    public function pengajuanPendanaan()
    {
        return $this->hasManyThrough(PengajuanPendanaan::class, SumberPendanaan::class, 'id_periode_pendanaan', 'id_sumber_pendanaan');
    }

    /**
     * Relasi ke sumber pendanaan
     */
    public function sumberPendanaan()
    {
        return $this->hasMany(SumberPendanaan::class, 'id_periode_pendanaan');
    }
}
