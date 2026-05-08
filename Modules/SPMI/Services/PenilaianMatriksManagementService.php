<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\TargetSkor;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

class PenilaianMatriksManagementService
{
    /**
     * @var PenilaianMatriks
     */
    protected $model = PenilaianMatriks::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianMatriks;
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
        $filter[2] = [
            'field' => 'apakah_data_default',
            'value' => 'IKU',
        ];
        $sql = "SELECT
                    pm.id,
                    pm.pertanyaan_penilaian,
                    pm.kategori_penilaian,
                    pm.jenis_penilaian,
                    pm.referensi_penilaian,
                    pm.bobot_penilaian,
                    pm.apakah_data_default,
                    pm.apakah_aktif AS apakah_aktif_matriks,
                    pm.info_level
                FROM " . $this->model->getTable() . " pm";

        // Defaultnya order berdasarkan info left
        if (!empty($order) && $order['no'] == 1) {
            $order = ['field' => 'pm.info_left', 'direction' => 'asc', 'desc' => false];
        }

        // Remove filter if empty
        foreach ($filter as $key => $obj) {
            if (!isset($obj['field'])) {
                continue;
            }
            if ($obj['field'] == 'jenis_penilaian' && $obj['value'] == 1) {
                unset($filter[$key]);
            }
        }

        $fieldMap = [
            'referensi_penilaian' =>
            "CASE WHEN pm.referensi_penilaian = 'pr' THEN 'Laporan Kinerja'
                WHEN pm.referensi_penilaian = 'se' THEN 'Evaluasi Diri' END",
            'bobot_penilaian' => 'pm.bobot_penilaian::text',
            'apakah_aktif_matriks' => "CASE WHEN pm.apakah_aktif THEN 'Aktif' ELSE 'Tidak Aktif' END",
            'apakah_data_default' => 'CASE WHEN pm.apakah_data_default = true THEN \'IKU\' ELSE \'IKT\' END',
        ];

        $defaultFilter = "pm.waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan semua matrix berdasarkan assessment guide.
     *
     * @param int $assessmentGuideId
     * @return mixed
     */
    public function showAllMatricesByPenilaianPanduan(int $assessmentGuideId): mixed
    {
        $table = $this->model->getTable();
        $sql = "SELECT
                    pm.id,
                    pm.nomor_penilaian,
                    pm.pertanyaan_penilaian,
                    pm.kategori_penilaian,
                    pm.id_parent,
                    pm.apakah_data_default,
                    pm.info_level,
                    JSON_AGG(
                        CASE WHEN pmp.id_penilaian_matriks IS NOT NULL THEN
                            json_build_object(
                                'id', pmp.id,
                                'nilai', pmp.nilai,
                                'deskripsi', pmp.deskripsi,
                                'apakah_nonaktif', pmp.apakah_nonaktif
                            )
                        ELSE null END
                    ) skor_indikator
                FROM $table pm
                LEFT JOIN spmi.penilaian_matriks_predikat pmp ON pmp.id_penilaian_matriks = pm.id
                    AND pmp.waktu_dihapus is null
                WHERE pm.waktu_dihapus is null AND pm.id_penilaian_panduan = ?
                GROUP BY pm.id, pm.pertanyaan_penilaian, pm.kategori_penilaian,
                    pm.info_left, pmp.id_penilaian_matriks
                ORDER BY pm.info_left ASC";

        $data = DB::select($sql, [$assessmentGuideId]);

        return $data;
    }

    public function showMatrixScores(int $PenilaianMatriksId): mixed
    {
        $sql = "SELECT
                    pmp.id,
                    skmpp.nilai,
                    pmp.rumus_penilaian,
                    pmp.kriteria,
                    pmp.deskripsi,
                    pmp.apakah_nonaktif
                FROM spmi.penilaian_matriks_predikat pmp
                JOIN spmi.skor_matriks_predikat_penilaian skmpp
                    ON skmpp.id = pmp.id_skor_matriks_predikat_penilaian
                WHERE pmp.id_penilaian_matriks = ?
                    AND pmp.waktu_dihapus is null
                ORDER BY skmpp.nilai DESC";
        $data = DB::select($sql, [$PenilaianMatriksId]);

        return $data;
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return PenilaianMatriks
     */
    public function show(int $id): PenilaianMatriks
    {
        $model = $this->model->findOrFail($id);
        $this->setHakAksesIKT($model);

        return $model;
    }

    public function customDetailPage($id)
    {
        $matrik = PenilaianMatriks::findOrFail($id);
        $penilaianMatriksScores = PenilaianMatriksPredikat::join('spmi.skor_matriks_predikat_penilaian as skmpp', 'skmpp.id', '=', 'spmi.penilaian_matriks_predikat.id_skor_matriks_predikat_penilaian')
            ->where('spmi.penilaian_matriks_predikat.id_penilaian_matriks', $id)
            ->orderBy('skmpp.nilai', 'desc')
            ->get(['spmi.penilaian_matriks_predikat.*', 'skmpp.nilai']);
        $skorMatriksPredikat = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $matrik->id_penilaian_panduan)
            ->orderBy('nilai', 'asc')
            ->pluck('deskripsi', 'nilai')
            ->toArray();
        return [
            'PenilaianMatriksScores' => $penilaianMatriksScores,
            'scoreOptions' => $skorMatriksPredikat
        ];
    }

    // Set akses IKT
    protected function setHakAksesIKT($model)
    {
        if ($model->apakah_data_default) {
            // set permission in request
            $permission = request()->permission;
            $permission['put'] = false;
            request()->merge(['permission' => $permission]);
        }
    }
}
