<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class DokumenUnit extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.dokumen_unit';

    protected $fillable = [
        'id_dokumen_perizinan',
        'id_unit_kerja'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_dokumen_perizinan' => ['required' => true, 'options' => DokumenPerizinan::class],
        'id_unit_kerja' => ['required' => true, 'options' => UnitKerja::class]
    ];
}
