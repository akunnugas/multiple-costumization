<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class AkreditasiBuku extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'kode_buku asc';
    const OPTION_COLUMN = 'nama_buku';

    const PERFORMANCE_REPORT = 'pr';
    const SELF_EVALUATION = 'se';

    const TYPE = [
        self::PERFORMANCE_REPORT => 'Laporan Kinerja',
        self::SELF_EVALUATION => 'Evaluasi Diri',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.akreditasi_buku';

    protected $fillable = [
        'kode_buku',
        'nama_buku',
        'jenis_buku',
        'apakah_data_default',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_buku' => ['required' => true, 'maxlength' => 5],
        'nama_buku' => ['required' => true, 'maxlength' => 255],
        'jenis_buku' => ['required' => true, 'maxlength' => 3],
        'apakah_data_default' => ['required' => true, 'boolean' => true],
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\AkreditasiBukuFactory::new();
    }
}
