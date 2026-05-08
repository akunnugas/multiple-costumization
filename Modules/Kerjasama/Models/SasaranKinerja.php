<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SasaranKinerja extends IndonesianModel
{
    use SoftDeletes;

    const OPTION_ORDER = 'sasaran asc';
    const OPTION_COLUMN = 'sasaran';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.sasaran_kinerja';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'sasaran',
        'keterangan',
        'level',
        'isian_default'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'sasaran' => ['required' => true, 'unique_ci' => true],
        'keterangan' => ['required' => false, 'control' => 'textarea'],
        'level' => ['required' => false],
    ];

    public function indikator(): HasMany
    {
        return $this->hasMany(IndikatorSasaran::class, 'id_sasaran_kinerja');
    }
}
