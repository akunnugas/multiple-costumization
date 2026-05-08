<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;

class Evaluasi extends IndonesianModel
{
    use SoftDeletes;

    protected $table = 'kerjasama.evaluasi';

    protected $fillable = [
        'uuid',
        'judul_evaluasi',
        'tipe_evaluasi',
        'tipe_model',
        'model_id',
        'id_mitra',
        'id_induk_kerjasama',
        'nama',
        'is_published',
        'mulai',
        'selesai',
    ];
    
    const TIPE_EVALUASI_OPTIONS = [
        'pt_ke_mitra' => 'PT ke Mitra',
        'mitra_ke_pt' => 'Mitra ke PT',
    ];

    const PUBLICATION_STATUS = [
        1 => 'Dipublikasikan',
        0 => 'Draft',
    ];
    
    protected $casts = [
        'is_published' => 'integer',
        'mulai' => 'date:Y-m-d',
        'selesai' => 'date:Y-m-d',
    ];
    
    const RULES = [
        'judul_evaluasi' => ['required' => true, 'maxlength' => 255],
        'tipe_evaluasi' => ['required' => true, 'options' => self::TIPE_EVALUASI_OPTIONS, 'variant' => 'search'],
        'uuid' => ['required' => true],
        'tipe_model' => ['required' => true, 'maxlength' => 255],
        'model_id' => ['required' => true, 'type' => 'integer', 'variant' => 'search'],
        'id_mitra' => ['required' => true, 'type' => 'integer', 'variant' => 'search'],
        'is_published' => ['required' => true, 'type' => 'boolean', 'options' => self::PUBLICATION_STATUS, 'variant' => 'search'],
        'mulai' => ['type' => 'date', 'defaultTypeDate' => 'd F Y', 'required' => true, 'column' => '6'],
        'selesai' => ['type' => 'date', 'defaultTypeDate' => 'd F Y', 'validation' => 'after_or_equal:mulai', 'required' => true, 'column' => '6'],
    ];

    public function pertanyaan(): HasMany
    {
        return $this->hasMany(Pertanyaan::class, 'evaluasi_id');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(Peserta::class, 'evaluasi_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'tipe_model', 'model_id');
    }

    public static function getTipeEvaluasiLabelAttribute($evaluasi)
    {
        $options = [
            'pt_ke_mitra' => 'PT ke Mitra',
            'mitra_ke_pt' => 'Mitra ke PT',
        ];
        return $options[$evaluasi] ?? 'Tidak Diketahui';
    }

    public function getTipeEvaluasiLabelFormatted()
    {
        return self::TIPE_EVALUASI_OPTIONS[$this->tipe_evaluasi] ?? 'Tidak Diketahui';
    }
}
