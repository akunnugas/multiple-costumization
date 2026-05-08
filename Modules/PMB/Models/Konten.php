<?php

namespace Modules\PMB\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class Konten extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.konten';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul_konten',
        'isi_konten',
        'informasi_tambahan',
        'jenis_konten',
        'id_file_gambar',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'judul_konten' => ['maxlength' => 255], // Judul Konten
        'isi_konten' => [], // Isi Konten
        'informasi_tambahan' => ['maxlength' => 255], // Keterangan
        'jenis_konten' => ['required' => true, 'maxlength' => 10], // Tipe Konten (alumni, fasilitas, event)
        'id_file_gambar' => [], // Gambar Konten (DMS)
    ];
}
