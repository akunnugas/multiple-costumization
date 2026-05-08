<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\UnitKerja;

class PengisianIndikator extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.pengisian_indikator';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_audit_periode',
        'id_lembaga_akreditasi',
        'id_pengisian_panduan',
        'id_unit',
        'id_jadwal_audit',
        'jenis_indikator',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_audit_periode' => ['required' => true, 'options' => AuditPeriode::class], // Tahun Audit
        'id_lembaga_akreditasi' => ['required' => true, 'options' => LembagaAkreditasi::class], // Lembaga Akreditasi
        'id_pengisian_panduan' => ['required' => true, 'options' => PengisianPanduan::class], // Panduan Pengisian
        'id_unit' => ['required' => true, 'options' => UnitKerja::class], // Program Studi
    ];

    public static function findByStudyProgramIdAndType($idPeriodeAudit, $idPengisianPanduan, $idUnit, $type, $idJadwalAudit = null)
    {
        return self::where('id_unit', $idUnit)
            ->where('id_audit_periode', $idPeriodeAudit)
            ->where('id_pengisian_panduan', $idPengisianPanduan)
            ->where('jenis_indikator', $type)
            ->when($idJadwalAudit, fn ($q) => $q->where('id_jadwal_audit', $idJadwalAudit))
            ->first();
    }

    public static function findByStudyProgram($studyProgramId, $auditPeriode, $PengisianPanduanID)
    {
        $sql = "SELECT
                concat(d.kode_jenjang, ' - ', o.nama_unit) as nama_unit,
                aa.nama_singkat_lembaga nama_lembaga_akreditasi,
                fg.nama_pengisian_panduan as panduan_pengisian,
                o.id id_unit,
                aa.id id_lembaga_akreditasi,
                fg.id id_pengisian_panduan,
                ja.tanggal_awal_pengisian,
                ja.tanggal_akhir_pengisian,
                d.kode_jenjang,
                d.id id_jenjang_pendidikan
            FROM core.unit_kerja o
            LEFT JOIN spmi.audit_periode ap ON ap.id = ?
                AND ap.waktu_dihapus IS NULL
            LEFT JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
                AND ja.waktu_dihapus IS NULL
            LEFT JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = ja.id
                AND aso.id_unit = o.id
            LEFT JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
            LEFT JOIN spmi.pengisian_panduan fg on
                (
                    CASE WHEN fg.id_jenjang_pendidikan IS NULL
                        THEN true
                        ELSE fg.id_jenjang_pendidikan = d.id
                    END
                )
                AND fg.apakah_aktif = true and fg.id = ?
                AND fg.waktu_dihapus IS NULL
            LEFT JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                AND aa.waktu_dihapus IS NULL
            WHERE o.id = ?
                AND o.waktu_dihapus IS NULL
            LIMIT 1";

        $data = DB::select($sql, [$auditPeriode, $PengisianPanduanID, $studyProgramId]);

        return $data;
    }

    public static function findByStudyAndPerformanceReport($studyProgramId, $auditPeriode)
    {
        if (Cstr::isEmpty($studyProgramId) || Cstr::isEmpty($auditPeriode)) {
            return null;
        }

        $sql = "SELECT
                fg.id id_pengisian_panduan,
                fg.nama_pengisian_panduan filling_guide,
                fg.tipe_edisi edition_type,
                fg.id_lembaga_akreditasi lembaga_akreditasi_id,
                fg.apakah_data_default
            FROM core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.id = ?
                AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
                AND ja.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = ja.id
                AND aso.id_unit = o.id
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
            JOIN spmi.pengisian_panduan fg on fg.id = aso.id_pengisian_panduan
            JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                AND aa.waktu_dihapus IS NULL
            WHERE o.id = ?
                AND o.waktu_dihapus IS NULL
            LIMIT 1";

        $data = DB::select($sql, [$auditPeriode, $studyProgramId]);

        return collect($data)->first();
    }

    public static function findByStudyAndSelfEvaluation($studyProgramId, $auditPeriode)
    {
        if (Cstr::isEmpty($studyProgramId) || Cstr::isEmpty($auditPeriode)) {
            return null;
        }

        $sql = "SELECT
                fg2.id id_pengisian_panduan,
                fg2.nama_pengisian_panduan filling_guide,
                fg2.tipe_edisi edition_type,
                fg2.id_lembaga_akreditasi lembaga_akreditasi_id,
                fg2.apakah_data_default
            FROM core.unit_kerja o
            JOIN spmi.audit_periode ap ON ap.id = ?
                AND ap.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit ja ON ja.id_audit_periode = ap.id
                AND ja.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_jadwal_audit = ja.id
                AND aso.id_unit = o.id
            JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
            JOIN spmi.pengisian_panduan fg on fg.id = aso.id_pengisian_panduan
            JOIN spmi.pengisian_panduan fg2 on fg2.id = fg.id_pengisian_panduan
            JOIN core.lembaga_akreditasi aa ON aa.id = fg.id_lembaga_akreditasi
                AND aa.waktu_dihapus IS NULL
            WHERE o.id = ?
                AND o.waktu_dihapus IS NULL
            LIMIT 1";

        $data = DB::select($sql, [$auditPeriode, $studyProgramId]);

        return collect($data)->first();
    }

    /**
     * get records by audit periode, accreditation agency, filling guide, study program
     * @param $auditPeriode
     * @param $accreditationAgency
     * @param $fillingGuide
     * @param $studyProgram
     *
     */
    public static function getRecordByFilter($auditPeriode, $accreditationAgency, $fillingGuide, $studyProgram, $isLED = false, $idJadwalAudit = null)
    {
        $data = self::where([
            'id_audit_periode' => $auditPeriode,
            'id_lembaga_akreditasi' =>  $accreditationAgency,
            'id_pengisian_panduan' => $fillingGuide,
            'id_unit' => $studyProgram
        ])->when($idJadwalAudit, fn ($q) => $q->where('id_jadwal_audit', $idJadwalAudit))
        ->first();

        if (!$data) {
            return [];
        }

        if (!$isLED) {
            $records = DataPengisianLK::getRecordByPengisianIndikator($data->id)->toArray();
            $records = Cstr::toMap('id_indikator_laporan_kinerja', 'data_pengisian_lk', $records);
        } else {
            $records = DataPengisianLED::getRecordByPengisianIndikator($data->id)->toArray();
            $records = Cstr::toMap('id_indikator_evaluasi_diri', 'data_pengisian_led', $records);
        }

        return $records;
    }
}
