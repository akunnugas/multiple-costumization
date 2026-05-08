<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class DokumenVersi extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.dokumen_versi';

    protected $fillable = [
        'id_dokumen',
        'versi',
        'alamat_berkas',
        'extension',
        'ukuran',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_dokumen' => ['required' => true, 'options' => Dokumen::class],
        'versi' => ['required' => true],
        'alamat_berkas' => ['required' => true, 'maxlength' => 255],
        'extension' => ['required' => true, 'maxlength' => 10],
        'ukuran' => ['required' => true],
    ];
}
