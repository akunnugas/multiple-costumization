<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisDokumen extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jenis_dokumen';

    protected $fillable = [
        'nama_jenis_dokumen', 'kode_jenis_dokumen', 'apakah_statis',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_jenis_dokumen' => ['required' => true, 'unique' => true, 'maxlength' => 255], // Nama Jenis Dokumen
        'kode_jenis_dokumen' => ['required' => true, 'unique' => true, 'maxlength' => 10], // Kode Jenis Dokumen
        'apakah_statis' => ['required' => true, 'type' => 'boolean'], // Tidak Bisa Diubah
    ];
}
