<?php

namespace Modules\Core\Models;

use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\User;

class DokumenPribadi extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.dokumen_pribadi';

    protected $fillable = [
        'id_biodata', 'id_dokumen', 'id_jenis_dokumen', 'status_dokumen', 'catatan_validasi', 'waktu_validasi', 'divalidasi_oleh'
    ];

    /**
     * Status dokumen
     */
    const STATUS_NOT_VALID = 'not_valid';
    const STATUS_ON_REVIEW = 'on_review';
    const STATUS_VALID = 'valid';
    const STATUSES = [
        self::STATUS_NOT_VALID => 'Belum Valid',
        self::STATUS_ON_REVIEW => 'Sedang Direview',
        self::STATUS_VALID => 'Valid',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_biodata' => ['required' => true, 'options' => Biodata::class], // Pengguna
        'id_dokumen' => ['required' => true, 'options' => Dokumen::class], // Dokumen
        'id_jenis_dokumen' => ['required' => true, 'options' => JenisDokumen::class], // Jenis Dokumen
        'status_dokumen' => ['required' => true, 'maxlength' => 20, 'options' => self::STATUSES], // Status Dokumen
        'catatan_validasi' => ['maxlength' => 255], // Catatan Validasi
        'waktu_validasi' => ['required' => false, 'type' => 'timestamp',
            'validation' => 'date_format:Y-m-d H:i:sO'], // Waktu Validasi
        'divalidasi_oleh' => ['required' => false, 'options' => User::class], // User yang melakukan validasi
    ];
}
