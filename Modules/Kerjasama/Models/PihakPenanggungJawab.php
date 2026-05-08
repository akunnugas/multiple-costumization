<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class PihakPenanggungJawab extends IndonesianModel
{
    use SoftDeletes;
    
    const MITRA = 'mitra';
    CONST UNITKERJA = 'unit';

    CONST PIHAK_LABEL = [
        self::MITRA => "Mitra",
        self::UNITKERJA => "Unit Kerja"
    ];

    CONST MAPPING_MODEL = [
        Mitra::class => self::MITRA,
        UnitKerja::class => self::UNITKERJA
    ];

    CONST MAPPING_PIHAK_VALUE = [
        self::MITRA => "nama_mitra",
        self::UNITKERJA => "nama_unit"
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.pihak_penanggung_jawab';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'model',
        'model_id',
        'id_pihak',
        'pihak_ke',
        'model_pihak',
        'alamat',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'model' => ['required' => true],
        'model_id' => ['required' => true],
        'pihak_ke' => ['validation' => 'numeric'],
        'id_pihak' => ['required' => 'true'],
        'model_pihak' => ['required' => true, 'validation' => 'max:255'],
        'alamat' => ['required' => false]
    ];

        
    /**
     * Ini adalah fungsi untuk mendifinisikan morp dari 
     * kerjasama atau kegiatan
     *
     * @return MorphTo
     */
    public function model(): MorphTo 
    {
        return $this->morphTo();
    }

    public function penanggung_jawab(): HasMany
    {
        return $this->hasMany(PenanggungJawab::class);
    }
}
