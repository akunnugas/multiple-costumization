<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;

class TarikDataLK extends IndonesianModel
{
    use HasFactory;

    protected $table = 'spmi.spmi_tarikdata_lk';

    protected $fillable = [
        'id_indikator_laporan_kinerja',
        'apakah_kurikulum',
        'sumber',
    ];
}
