<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PenanggungJawab extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'nama_penanggung_jawab asc';
    const OPTION_COLUMN = 'nama_penanggung_jawab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.penanggung_jawab';

    protected $fillable = [
        'nama_penanggung_jawab',
        'email',
        'telepon',
        'jabatan',
        'id_pihak_penanggung_jawab',
        'nip'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_penanggung_jawab' => ['required' => true, 'maxlength' => 255],
        'email' => ['type' => 'email'],
        'telepon' => ['type' => 'number'],
        'jabatan' => [],
        'nip' => [],
        'id_pihak_penanggung_jawab' => ['required' => true]
    ];

    // protected static function newFactory()
    // {
    //     return \Modules\Kerjasama\Database\factories\PenanggungJawabFactory::new();
    // }
}
