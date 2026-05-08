<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class Peserta extends IndonesianModel
{
    use SoftDeletes;

    protected $table = 'kerjasama.peserta';

    protected $fillable = [
        'unit_kerja_id',
        'evaluasi_id',
        'nama',
        'phone',
        'email',
        'nik',
        'npwp',
    ];
    // protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const RULES = [
        'unit_kerja_id' => ['nullable' => true, 'type' => 'integer'],
        'evaluasi_id' => ['required' => true, 'type' => 'integer'],
        'nama' => ['nullable' => true, 'maxlength' => 255],
        'phone' => ['nullable' => true, 'maxlength' => 255, 'type' => 'numeric'],
        'email' => ['nullable' => true, 'maxlength' => 255, 'type' => 'email'],
        'nik' => ['nullable' => true, 'maxlength' => 255, 'type' => 'numeric'],
        'npwp' => ['nullable' => true, 'maxlength' => 255],
    ];

    public function evaluasi()
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanPeserta::class, 'peserta_id');
    }   
    
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }
}
