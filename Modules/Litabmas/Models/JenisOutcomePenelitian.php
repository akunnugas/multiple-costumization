<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;
use Modules\Litabmas\Database\factories\JenisOutcomePenelitianFactory;
use Modules\Litabmas\Models\Cache\JenisOutcomePenelitianCache;

class JenisOutcomePenelitian extends IndonesianModel
{
    use HasFactory, SoftDeletes, ClearCache;

    const OPTION_COLUMN = 'nama_outcome';
    const OPTION_ORDER = 'nama_outcome asc';


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.jenis_outcome_penelitian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_jenis_publikasi',
        'nama_outcome',
        'kategori_outcome',
        'batas_pengumpulan_outcome',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_jenis_publikasi' => ['required' => true, 'options' => JenisPublikasi::class],                                              // Jenis Outcome
        'nama_outcome' => ['required' => true, 'maxlength' => 255, 'unique' => true],                                                  // Nama Outcome
        'kategori_outcome' => ['required' => true, 'maxlength' => 2, 'options' => self::CATEGORIES],                  // Kategori Outcome (sama kyk output)
        'batas_pengumpulan_outcome' => ['required' => true, 'numeric' => true, 'options' => self::COLLECTION_LIMIT_OPTIONS],           // Batas Pengumpulan
    ];

    /**
     * Kategori
     */
    const CATEGORY_PUBLIKASI = '01';
    const CATEGORY_PATEN_HKI = '02';
    const CATEGORY_PUBLIKASI_PATEN_HKI = '03';
    const CATEGORIES = [
        self::CATEGORY_PUBLIKASI => 'Publikasi',
        self::CATEGORY_PATEN_HKI => 'Paten/HKI',
        self::CATEGORY_PUBLIKASI_PATEN_HKI => 'Publikasi dan Paten/HKI',
    ];

    /**
     * Batas Pengumpulan options
     */
    const COLLECTION_LIMIT_OPTIONS = [
        1 => '1 Tahun',
        2 => '2 Tahun',
        3 => '3 Tahun',
    ];

    protected static function newFactory()
    {
        return JenisOutcomePenelitianFactory::new();
    }

    /**
     * Get list data output dari database (cache).
     *
     * @return Collection
     */
    public function getListCache()
    {
        return JenisOutcomePenelitianCache::get();
    }

    /**
     * When model is booted, do something.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            self::clearCache($model);
        });
    }

    /**
     * Clear/forgot cache.
     *
     * @param $model
     * @return void
     */
    private static function clearCache($model)
    {
        JenisOutcomePenelitianCache::destroy();
    }

    /**
     * Relasi ke master klaster Pendanaan.
     * .
     */
    public function klasterPendanaan(){
        return $this->belongsToMany(KlasterPendanaan::class, 'litabmas.klaster_pendanaan_outcome_penelitian', 'id_jenis_outcome_penelitian', 'id_klaster_pendanaan');
    }
}
