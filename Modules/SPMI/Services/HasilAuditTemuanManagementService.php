<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianSkor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Models\TinjauanTemuan;

class HasilAuditTemuanManagementService
{
    /**
     * @var AuditTemuan
     */
    protected $model = AuditTemuan::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new AuditTemuan;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $kategori = AuditTemuan::TYPES;
        $sql =
            "SELECT
                m.nomor_penilaian as id,
                m.pertanyaan_penilaian,
                af.jenis_temuan,
                CASE
                    WHEN af.jenis_temuan = '1' THEN '$kategori[1]'
                    WHEN af.jenis_temuan = '2' THEN '$kategori[2]'
                    WHEN af.jenis_temuan = '3' THEN '$kategori[3]'
                END jenis_temuan_label,
                count(DISTINCT ass.id) total_finding,
                ARRAY_AGG(DISTINCT concat(ass.id, '|', coalesce(jp.kode_jenjang || ' - ', ''), uk.nama_unit)) list_program_studi_temuan,
                asch.nama_jadwal_audit
            FROM spmi.audit_temuan af
            JOIN spmi.penilaian_matriks m ON m.id = af.id_penilaian_matriks
                AND m.waktu_dihapus IS NULL
            JOIN spmi.penilaian_audit ass ON ass.id = af.id_penilaian_audit
                AND ass.waktu_dihapus IS NULL
            JOIN core.unit_kerja uk ON uk.id = ass.id_unit
                AND uk.waktu_dihapus IS NULL
            JOIN core.jenjang_pendidikan jp ON jp.id = uk.id_jenjang_pendidikan
                AND jp.waktu_dihapus IS NULL
            JOIN spmi.jadwal_audit_unit aso ON aso.id_unit = uk.id
                AND aso.id_penilaian_panduan = ass.id_penilaian_panduan
            JOIN spmi.jadwal_audit asch ON asch.id = aso.id_jadwal_audit
                AND asch.id = ass.id_jadwal_audit
                AND asch.id_audit_periode = ass.id_audit_periode
                AND asch.apakah_audit_aktif = true
                AND asch.waktu_dihapus is null
            JOIN spmi.penilaian_panduan pp ON pp.id = aso.id_penilaian_panduan AND m.id_penilaian_panduan = pp.id
            JOIN spmi.surat_tugas_auditor sa ON sa.id_audit_periode = asch.id_audit_periode
                AND sa.waktu_dihapus is null
            JOIN spmi.surat_tugas_auditor_pegawai stlp ON stlp.id_unit = uk.id
                AND stlp.id_surat_tugas_auditor = sa.id
                AND stlp.waktu_dihapus is null
            ";

        $fieldMap = [
            'periode_audit' => 'ap.tahun_audit',
            'id_audit_periode' => 'ass.id_audit_periode',
            'id_unit_kerja' => 'ass.id_unit',
            'id_jenjang_pendidikan' => 'jp.id'
        ];

        $defaultFilter = "af.waktu_dihapus is null AND ass.apakah_penilaian_mandiri = false";

        $bindings = [];

        [$sql, $bindings] = Pagination::buildQuery(
            bindings: $bindings,
            query: $sql,
            order: 'af.jenis_temuan DESC, total_finding DESC',
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            groupBy: 'm.nomor_penilaian, m.pertanyaan_penilaian, af.jenis_temuan, asch.nama_jadwal_audit',
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return AuditTemuan
     */
    public function show(int $id): AuditTemuan
    {
        return $this->model->findOrFail($id);
    }
}
