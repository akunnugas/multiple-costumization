<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Jurusan extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jurusan';

    protected $fillable = ['id_jenjang_pendidikan','nama_jurusan', 'kode_jurusan',];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_jenjang_pendidikan' => ['required' => true],
        'kode_jurusan' => ['required' => true, 'maxlength' => 20],
        'nama_jurusan' => ['required' => true, 'maxlength' => 100],
    ];
}
