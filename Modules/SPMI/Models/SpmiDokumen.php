<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class SpmiDokumen extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'kode_spmi_dokumen asc';
    const OPTION_COLUMN = 'nama_spmi_dokumen';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.spmi_dokumen';

    protected $fillable = [
        'kode_spmi_dokumen',
        'nama_spmi_dokumen',
        'deskripsi',
        'id_dokumen',
        'versi',
        'id_jenis',
        'apakah_aktif',
        'tanggal_awal_berlaku',
        'tanggal_akhir_berlaku',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_spmi_dokumen' => ['required' => true, 'maxlength' => 50, 'unique' => true], // Kode Dokumen
        'nama_spmi_dokumen' => ['required' => true, 'maxlength' => 255], // Nama Dokumen
        'deskripsi' => ['required' => false, 'maxlength' => 255], // Deskripsi Singkat
        'id_dokumen' => [
            'required' => true, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 15000
        ], // ID Dokumen
        'versi' => ['required' => true, 'maxlength' => 5, 'type' => 'numeric'], // Versi Dokumen
        'id_jenis' => ['required' => true, 'options' => SpmiJenisDokumen::class], // ID Tipe Dokumen
        'apakah_aktif' => ['required' => true, 'type' => 'boolean'], // Status Dokumen
        'tanggal_awal_berlaku' => ['required' => true, 'type' => 'date'], // Tanggal Mulai Berlaku
        'tanggal_akhir_berlaku' => ['required' => false, 'type' => 'date'], // Tanggal Akhir Berlaku
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\SpmiDokumenFactory::new();
    }
}
