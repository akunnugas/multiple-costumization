<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class JadwalAudit extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.jadwal_audit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'nama_jadwal_audit',
        'tanggal_awal_pengisian',
        'tanggal_akhir_pengisian',
        'tanggal_awal_penilaian',
        'tanggal_akhir_penilaian',
        'apakah_audit_aktif',
        'apakah_penilaian_mandiri',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Tahun Audit
        'nama_jadwal_audit' => ['required' => true, 'label' => 'Nama Kegiatan AMI'], // Nama Jadwal Audit
        'tanggal_awal_pengisian' => ['required' => true, 'type' => 'date'], // Tanggal Mulai Pengisian
        'tanggal_akhir_pengisian' => ['required' => true, 'type' => 'date', 'validation' => 'after_or_equal:tanggal_awal_pengisian'], // Tanggal Akhir Pengisian
        'tanggal_awal_penilaian' => ['required' => true, 'type' => 'date', 'validation' => 'after:tanggal_akhir_pengisian'], // Tanggal Mulai Penilaian
        'tanggal_akhir_penilaian' => ['required' => true, 'type' => 'date', 'validation' => 'after_or_equal:tanggal_awal_penilaian'], // Tanggal Akhir Penilaian
        'apakah_audit_aktif' => ['required' => true, 'type' => 'boolean'], // Status Audit
        'apakah_penilaian_mandiri' => ['required' => true, 'type' => 'boolean'], // Penilaian Mandiri
    ];

    public function fillingDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->tanggal_awal_pengisian . '|' . $this->tanggal_akhir_pengisian;
            }
        );
    }

    public function assessmentDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->tanggal_awal_penilaian . '|' . $this->tanggal_akhir_penilaian;
            }
        );
    }

    public function unit(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->organizations->pluck('id')->toArray();
            }
        );
    }

    public function organizations()
    {
        return $this->belongsToMany(
            UnitKerja::class,
            'spmi.jadwal_audit_unit',
            'id_jadwal_audit',
            'id_unit'
        );
    }

    public function periode()
    {
        return $this->belongsTo(AuditPeriode::class, 'id_audit_periode', 'id');
    }

    public function jadwalAuditUnit()
    {
        return $this->hasMany(JadwalAuditUnit::class, 'id_jadwal_audit', 'id');
    }
}
