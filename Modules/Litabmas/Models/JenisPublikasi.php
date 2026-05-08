<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Database\factories\JenisPublikasiFactory;

class JenisPublikasi extends IndonesianModel
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.jenis_publikasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_jenis_publikasi',
        'ref_key_siakad'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_jenis_publikasi' => ['required' => true, 'maxlength' => 255], // Nama Output
        'ref_key_siakad' => ['maxlength' => 255, 'unique' => true] // Kolom PK SIAKAD V1
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_jenis_publikasi asc';
    const OPTION_COLUMN = 'nama_jenis_publikasi';

    protected static function newFactory()
    {
        return JenisPublikasiFactory::new();
    }
}
