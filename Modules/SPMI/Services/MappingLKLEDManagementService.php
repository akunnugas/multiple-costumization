<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AuditPeriode;

class MappingLKLEDManagementService extends Service
{
    /**
     * @var PenilaianMatriksPredikat
     */
    protected $model = PenilaianMatriksPredikat::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PenilaianMatriksPredikat;
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
        $sql = "WITH combined_mappings AS (
                SELECT
                    lk.id_unit,
                    lk.id_audit_periode,
                    il.id_pengisian_panduan,
                    pp.nama_singkat AS nama_panduan
                FROM spmi.mapping_lk lk
                JOIN spmi.indikator_laporan_kinerja il ON il.id = lk.id_indikator_laporan_kinerja
                JOIN spmi.pengisian_panduan pp ON pp.id = il.id_pengisian_panduan AND pp.waktu_dihapus IS NULL
                WHERE lk.id_audit_periode = ?

                UNION ALL

                SELECT
                    led.id_unit,
                    led.id_audit_periode,
                    il.id_pengisian_panduan,
                    pp.nama_singkat AS nama_panduan
                FROM spmi.mapping_led led
                JOIN spmi.indikator_evaluasi_diri il ON il.id = led.id_indikator_evaluasi_diri
                JOIN spmi.pengisian_panduan pp ON pp.id = il.id_pengisian_panduan AND pp.waktu_dihapus IS NULL
                WHERE led.id_audit_periode = ?
            ),
            aggregated_mappings AS (
                SELECT
                    id_unit,
                    id_audit_periode,
                    STRING_AGG(DISTINCT nama_panduan, ', ') as nama_pengisian_panduan
                FROM combined_mappings
                GROUP BY id_unit, id_audit_periode
            )
            SELECT
                o.id,
                o.kode_unit,
                CONCAT(COALESCE(d.kode_jenjang || ' - ', ''), o.nama_unit, ' (', o.kode_unit, ')') AS nama_unit,
                po.id AS id_parent,
                o.jenis_unit,
                o.info_level,
                o.info_left,
                o.info_right,
                m.id_audit_periode,
                m.nama_pengisian_panduan,
                CASE WHEN m.id_unit IS NOT NULL THEN 1 ELSE 0 END as is_mapping
            FROM core.unit_kerja o
            LEFT JOIN core.jenjang_pendidikan d ON d.id = o.id_jenjang_pendidikan
            LEFT JOIN core.unit_kerja po ON po.id = o.id_parent
            LEFT JOIN aggregated_mappings m ON m.id_unit = o.id";

        $fieldMap = [
            'id' => 'o.id',
            'nama_unit' => 'o.nama_unit',
            'kode_unit' => 'o.kode_unit',
            'id_parent' => 'po.id',
            'jenis_unit' => 'o.jenis_unit',
        ];

        $idauditperiode = $filter[0]['value'] ?? null;
        $bindings = [$idauditperiode, $idauditperiode];
        unset($filter[0]);

        $order = ['field' => 'o.info_left'];
        $defaultFilter = "o.waktu_dihapus is null AND o.apakah_aktif = true";

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap,
            bindings: $bindings,
        );

        $temp = [];
        foreach ($bindings as $key => $value) {
            $temp[] = (int) $value;
        }
        $bindings = $temp;

        $flatList = DB::select($sql, $bindings);
        $flatList = array_map("unserialize", array_unique(array_map("serialize", $flatList)));

        if (empty($flatList)) {
            return [];
        }

        $nodesById = [];
        $nodesByParentId = [];

        foreach ($flatList as $row) {
            $node = (object) $row;
            if (empty($node->id_audit_periode)) {
                $node->id_audit_periode = $idauditperiode;
            }
            $node->children = [];

            $nodesById[$node->id] = $node;
            $nodesByParentId[$node->id_parent][] = $node;
        }

        $tree = [];
        foreach ($nodesById as $id => $node) {
            if ($node->id_parent && isset($nodesById[$node->id_parent])) {
                $nodesById[$node->id_parent]->children[] = $node;
            } else {
                $tree[] = $node;
            }
        }

        $this->sortTreeByInfoLeft($tree);

        return $tree;
    }

    private function sortTreeByInfoLeft(array &$nodes): void
    {
        usort($nodes, fn($a, $b) => $a->info_left <=> $b->info_left);
        foreach ($nodes as $node) {
            if (!empty($node->children)) {
                $this->sortTreeByInfoLeft($node->children);
            }
        }
    }

    public function show($id)
    {
        return DB::table('core.unit_kerja as u')
            ->leftJoin('core.jenjang_pendidikan as j', 'j.id', '=', 'u.id_jenjang_pendidikan')
            ->where('u.id', $id)
            ->select('u.*', 'j.nama_jenjang', 'j.kode_jenjang')
            ->first();
    }

    public function detail($idUnit, $idPeriode, $type)
    {
        $unit = UnitKerja::select(
            'id',
            'id_jenjang_pendidikan',
            'nama_unit',
            'jenis_unit',
            'info_left',
            'info_right'
        )
            ->with('jenjang')->findOrFail($idUnit);
        $periode = AuditPeriode::findOrFail($idPeriode);

        $child_unit_options = [];
        if (in_array($unit->jenis_unit, [UnitKerja::UNIVERSITY, UnitKerja::FACULTY, UnitKerja::MAJOR])) {
            $child_unit = UnitKerja::select(
                'id',
                'id_jenjang_pendidikan',
                'nama_unit',
                'jenis_unit'
            )
                ->with('jenjang')
                ->where(function ($q) use ($unit) {
                    $q->where('id_parent', $unit->id)
                        ->orWhere(function ($q2) use ($unit) {
                            $q2->where('info_left', '>', $unit->info_left)
                                ->where('info_right', '<', $unit->info_right);
                        });
                })
                ->where('apakah_aktif', true)
                ->whereNotIn('jenis_unit', [UnitKerja::UNIVERSITY, UnitKerja::FACULTY, UnitKerja::MAJOR])
                ->get();

            foreach ($child_unit as $row) {
                $unit_name = $row->nama_unit;
                if ($row->jenjang) {
                    $unit_name = $row->jenjang->kode_jenjang . ' - ' . $unit_name;
                }
                $child_unit_options[$row->id] = $unit_name;
            }
        }

        $unit_name = $unit->nama_unit;
        if ($unit->jenjang) {
            $unit_name = $unit->jenjang->kode_jenjang . ' - ' . $unit_name;
        }

        $sqlByType = "FROM spmi.mapping_lk ml
            JOIN spmi.indikator_laporan_kinerja ilk ON ilk.id = ml.id_indikator_laporan_kinerja";

        if ($type == AkreditasiBuku::SELF_EVALUATION) {
            $sqlByType = "FROM spmi.mapping_led ml
                JOIN spmi.indikator_evaluasi_diri ilk ON ilk.id = ml.id_indikator_evaluasi_diri";
        }

        $pengisian_panduan = DB::select("SELECT
            pp.id, pp.nama_singkat
            $sqlByType
            JOIN spmi.pengisian_panduan pp ON pp.id = ilk.id_pengisian_panduan
            WHERE ml.id_audit_periode = :id_audit_periode
            AND ml.id_unit = :id_unit
            GROUP BY pp.id", ['id_audit_periode' => $idPeriode, 'id_unit' => $idUnit]);

        $pengisian_panduan_label = null;
        $pengisian_panduan_options = [];
        if (!empty($pengisian_panduan)) {
            $pengisian_panduan_label = implode(', ', array_map(function ($item) {
                return $item->nama_singkat;
            }, $pengisian_panduan));
            foreach ($pengisian_panduan as $item) {
                $pengisian_panduan_options[$item->id] = $item->nama_singkat;
            }
        }

        return [
            'unit_id' => $unit->id,
            'unit_name' => $unit_name,
            'periode_id' => $periode->id,
            'tahun_audit' => $periode->tahun_audit,
            'level' => $unit->jenis_unit,
            'pengisian_panduan_label' => $pengisian_panduan_label,
            'pengisian_panduan_options' => $pengisian_panduan_options,
            'child_unit_options' => $child_unit_options,
        ];
    }

    public function update($data, $id)
    {
        $payload['mapping'] = $data['checkedButir'] ?? [];
        $payload['id_pengisian_panduan'] = $data['id_pengisian_panduan'] ?? null;
        $payload['id_audit_periode'] = $data['auditPeriodId'] ?? null;
        $payload['id_unit'] = $data['unitId'] ?? null;
        if (isset($data['program_studi']) && is_array($data['program_studi'])) {
            $payload['id_unit'] = $data['program_studi'];
        }

        DB::beginTransaction();

        try {
            $service = $data['jenisEdisi'] == AkreditasiBuku::PERFORMANCE_REPORT
                ? new MappingLKManagementService()
                : new MappingLEDManagementService();

            $service->store($payload);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return new Error("Terjadi kesalahan saat menyimpan data");
        }

        return 1;
    }

    public function getListIndicatorED($idUnit, $idPeriode, $idPengisianPanduan)
    {
        return DB::select("select
            ied.id,
            ied.nama_indikator_evaluasi_diri as indikator,
            ied.info_level,
            ied.apakah_data_default
            from spmi.mapping_led ml
            join spmi.indikator_evaluasi_diri ied
                on ml.id_indikator_evaluasi_diri = ied.id
                and ied.id_pengisian_panduan = :id_pengisian_panduan
                and ied.waktu_dihapus is null
            where ml.id_unit = :id_unit
            and ml.id_audit_periode = :id_audit_periode
            and ied.waktu_dihapus is null
        ", [
            'id_unit' => $idUnit,
            'id_audit_periode' => $idPeriode,
            'id_pengisian_panduan' => $idPengisianPanduan,
        ]);
    }

    public function getListIndicatorLK($idUnit, $idPeriode, $idPengisianPanduan)
    {
        return DB::select("select
            ilk.id,
            ilk.nama_indikator_laporan_kinerja as indikator,
            ilk.info_level,
            ilk.apakah_data_default
            from spmi.mapping_lk ml
            join spmi.indikator_laporan_kinerja ilk
                on ml.id_indikator_laporan_kinerja = ilk.id
                and ilk.id_pengisian_panduan = :id_pengisian_panduan
                and ilk.waktu_dihapus is null
            where ml.id_unit = :id_unit
            and ml.id_audit_periode = :id_audit_periode
            and ilk.waktu_dihapus is null
        ", [
            'id_unit' => $idUnit,
            'id_audit_periode' => $idPeriode,
            'id_pengisian_panduan' => $idPengisianPanduan,
        ]);
    }
}
