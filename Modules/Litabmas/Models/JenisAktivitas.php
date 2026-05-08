<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Database\factories\JenisAktivitasFactory;

class JenisAktivitas extends IndonesianModel
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.jenis_aktivitas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_jenis_aktivitas',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_jenis_aktivitas' => ['required' => true, 'maxlength' => 255, 'unique' => true], // Nama jenis aktivitas
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_jenis_aktivitas asc';
    const OPTION_COLUMN = 'nama_jenis_aktivitas';

    protected static function newFactory()
    {
        return JenisAktivitasFactory::new();
    }
}
