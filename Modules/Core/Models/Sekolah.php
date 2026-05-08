<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Wilayah;

class Sekolah extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.sekolah';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'npsn',
        'id_kota',
        'id_jenis_institusi',
        'nama_sekolah',
        'alamat_sekolah',
        'rt_sekolah',
        'rw_sekolah',
        'kode_pos_sekolah',
        'telepon_sekolah',
        'email_sekolah',
        'website_sekolah',
        'akreditasi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'npsn' => ['required' => true, 'maxlength' => 255], // NPSN
        'id_kota' => ['required' => true, 'options' => Wilayah::class], // Kota
        'id_jenis_institusi' => ['required' => true, 'options' => JenisInstitusi::class], // Jenis Sekolah
        'nama_sekolah' => ['required' => true, 'maxlength' => 255], // Nama Sekolah
        'alamat_sekolah' => ['required' => true, 'maxlength' => 255], // Alamat
        'rt_sekolah' => ['maxlength' => 255], // RT
        'rw_sekolah' => ['maxlength' => 255], // RW
        'kode_pos_sekolah' => ['maxlength' => 255], // Kode Pos
        'telepon_sekolah' => ['maxlength' => 255], // No. Telp
        'email_sekolah' => ['maxlength' => 255, 'type' => 'email'], // Email
        'website_sekolah' => ['maxlength' => 255], // Website
        'akreditasi' => ['maxlength' => 255], // Accreditation
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_sekolah';
    const OPTION_COLUMN = 'nama_sekolah';

    /**
     * Display options by region level and parent id.
     *
     * @param int $cityId
     * @param string $orderBy
     * @return array
     */
    public static function optionsByCity(int $cityId, string $orderBy = self::OPTION_ORDER)
    {
        return self::query()->where('id_kota', $cityId)
            ->orderByRaw($orderBy)
            ->get(['id', static::OPTION_COLUMN, 'npsn'])
            ->map(function ($item) {
                return [
                    'value' => $item->id,
                    'label' => $item->npsn . ' - ' . $item->nama_sekolah,
                ];
            })
            ->pluck('label', 'value')
            ->toArray();

    }
}
