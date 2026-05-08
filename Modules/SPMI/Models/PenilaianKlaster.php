<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianKlaster extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_klaster';

    protected $fillable = [
        'kode_klaster',
        'nama_klaster',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_klaster' => ['required' => true],
        'nama_klaster' => ['required' => true],
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\PenilaianKlasterFactory::new();
    }
}
