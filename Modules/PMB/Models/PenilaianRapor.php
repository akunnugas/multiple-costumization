<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PenilaianRapor extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.penilaian_rapor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_mata_pelajaran',
        'kode_penilaian',
        'nama_penilaian',
        'jenis_penilaian',
        'keterangan_penilaian',
    ];

    const TYPE_AVERAGE = 'R';
    const TYPE_SCHOOL_ACCREDITATION = 'A';
    const TYPE_REGIONAL = 'D';
    const TYPE_ACHIEVEMENT = 'P';
    const TYPES = [
        self::TYPE_AVERAGE => 'Nilai Rata-Rata',
        self::TYPE_SCHOOL_ACCREDITATION => 'Akreditasi Sekolah',
        self::TYPE_REGIONAL => 'Daerah Asal Sekolah',
        self::TYPE_ACHIEVEMENT => 'Prestasi',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_mata_pelajaran' => ['required' => true, 'options' => MataPelajaran::class], // Mata Pelajaran
        'kode_penilaian' => ['required' => true, 'unique' => true, 'maxlength' => 20], // Kode Penilaian Rapor
        'nama_penilaian' => ['required' => true, 'maxlength' => 255], // Nama Penilaian Rapor
        'jenis_penilaian' => ['required' => true, 'maxlength' => 1, 'options' => self::TYPES], // Jenis Penilaian Rapor
        'keterangan_penilaian' => ['maxlength' => 500], // Keterangan Penilaian Rapor
    ];
}
