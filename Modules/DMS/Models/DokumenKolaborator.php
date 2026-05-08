<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Gate\Models\User;

class DokumenKolaborator extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.dokumen_kolaborator';

    protected $fillable = [
        'id_dokumen_perizinan',
        'id_user'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_dokumen_perizinan' => ['required' => true, 'options' => DokumenPerizinan::class],
        'id_user' => ['required' => true, 'options' => User::class]
    ];
}
