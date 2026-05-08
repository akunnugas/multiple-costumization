<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class SpmiJenisDokumen extends IndonesianModel
{
    const OPTION_ORDER = 'nama_spmi_jenis_dokumen asc';
    const OPTION_COLUMN = 'nama_spmi_jenis_dokumen';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.spmi_jenis_dokumen';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_spmi_jenis_dokumen',
        'deskripsi',
        'deskripsi_singkat',
        'alamat_berkas',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_spmi_jenis_dokumen' => ['required' => true, 'maxlength' => 255], // Nama Jenis Dokumen Mutu
        'deskripsi' => ['required' => false, 'maxlength' => 255], // Deskripsi Jenis Dokumen Mutu
        'deskripsi_singkat' => ['required' => false, 'maxlength' => 255], // Deskripsi Singkat Jenis Dokumen Mutu
        'alamat_berkas' => ['required' => false, 'maxlength' => 255], //  URL File Jenis Dokumen Mutu
    ];
}
