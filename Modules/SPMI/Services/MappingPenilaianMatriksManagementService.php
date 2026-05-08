<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;

class MappingPenilaianMatriksManagementService extends Service
{
    protected $model = MappingPenilaianMatriks::class;

    public function __construct()
    {
        $this->model = new MappingPenilaianMatriks;
    }

    public function index(int | null $page = null, int | null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $sql = "WITH combined_mappings AS (
                SELECT
                    mp.id_unit,
                    mp.id_audit_periode,
                    pm.id_penilaian_panduan,
                    pp.nama_singkat AS nama_panduan
                FROM spmi.mapping_penilaian_matriks mp
                JOIN spmi.penilaian_matriks pm ON pm.id = mp.id_penilaian_matriks AND pm.waktu_dihapus IS NULL
                JOIN spmi.penilaian_panduan pp ON pp.id = pm.id_penilaian_panduan AND pp.waktu_dihapus IS NULL
                WHERE mp.id_audit_periode = ?
            ),
            aggregated_mappings AS (
                SELECT
                    id_unit,
                    id_audit_periode,
                    STRING_AGG(DISTINCT nama_panduan, ', ') as nama_penilaian_panduan
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
                m.nama_penilaian_panduan,
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
        $bindings = [$idauditperiode];
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

    public function update(array $data)
    {
        $mappings = $data['checkedButir'] ?? [];

        $usedOldMappings = DB::table('spmi.target_skor as ts')
            ->join('spmi.penilaian_matriks as pm', 'pm.id', '=', 'ts.id_penilaian_matriks')
            ->join('spmi.target_indikator as ti', 'ti.id', '=', 'ts.id_target_indikator')
            ->where('pm.id_penilaian_panduan', $data['id_penilaian_panduan'])
            ->where('ti.id_audit_periode', $data['auditPeriodId'])
            ->when(isset($data['program_studi']) && is_array($data['program_studi']), function ($query) use ($data) {
                $query->whereIn('ti.id_unit', $data['program_studi']);
            }, function ($query) use ($data) {
                $query->where('ti.id_unit', $data['unitId']);
            })
            ->pluck('ts.id_penilaian_matriks')
            ->toArray();

        $removedUsedMappings = array_diff($usedOldMappings, $mappings);
        if (!empty($removedUsedMappings)) {
            $matriks = PenilaianMatriks::whereIn('id', $removedUsedMappings)->pluck('nomor_penilaian');
            $label = implode(', ', $matriks->toArray());
            return new Error("Matriks Penilaian ($label) sudah digunakan pada Atur Target Capaian. Silakan hapus target skor terlebih dahulu sebelum menghapus mapping pada matriks penilaian.");
        }

        DB::beginTransaction();

        // Hapus mapping sebelumnya
        $listIdMatriksPenilaian = PenilaianMatriks::where('id_penilaian_panduan', $data['id_penilaian_panduan'])
            ->pluck('id')
            ->toArray();
        $mappings = array_filter($mappings, function ($id) use ($listIdMatriksPenilaian) {
            return in_array($id, $listIdMatriksPenilaian);
        });

        MappingPenilaianMatriks::where('id_audit_periode', $data['auditPeriodId'])
            ->whereIn('id_penilaian_matriks', $listIdMatriksPenilaian)
            ->when(isset($data['program_studi']) && is_array($data['program_studi']), function ($query) use ($data) {
                $query->whereIn('id_unit', $data['program_studi']);
            }, function ($query) use ($data) {
                $query->where('id_unit', $data['unitId']);
            })
            ->delete();

        foreach ($mappings as $id) {
            try {
                // Buat mapping baru
                if (isset($data['program_studi']) && is_array($data['program_studi'])) {
                    foreach ($data['program_studi'] as $idUnit) {
                        MappingPenilaianMatriks::create([
                            'id_penilaian_matriks' => $id,
                            'id_audit_periode' => $data['auditPeriodId'],
                            'id_unit' => $idUnit,
                        ]);
                    }
                } else {
                    MappingPenilaianMatriks::create([
                        'id_penilaian_matriks' => $id,
                        'id_audit_periode' => $data['auditPeriodId'],
                        'id_unit' => $data['unitId'],
                    ]);
                }
            } catch (\Exception $e) {
                return $e;
            }
        }

        DB::commit();

        return 1;
    }

    public function detail($idUnit, $idPeriode)
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
                ->where(function ($w) use ($unit) {
                    $w->where(function ($q) use ($unit) {
                        $q->where('info_left', '>=', $unit->info_left)
                            ->where('info_right', '<=', $unit->info_right);
                    })->orWhere('id_parent', $unit->id);
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

        $penilaian_panduan = DB::select("SELECT
            pp.id, pp.nama_singkat
            from spmi.mapping_penilaian_matriks mpm
            join spmi.penilaian_matriks pm on mpm.id_penilaian_matriks = pm.id
            join spmi.penilaian_panduan pp on pm.id_penilaian_panduan = pp.id
            where mpm.id_audit_periode = :id_audit_periode
            and mpm.id_unit = :id_unit
            group by pp.id", ['id_audit_periode' => $idPeriode, 'id_unit' => $idUnit]);

        $penilaian_panduan_label = null;
        $penilaian_panduan_options = [];
        if (!empty($penilaian_panduan)) {
            $penilaian_panduan_label = implode(', ', array_map(function ($item) {
                return $item->nama_singkat;
            }, $penilaian_panduan));
            foreach ($penilaian_panduan as $item) {
                $penilaian_panduan_options[$item->id] = $item->nama_singkat;
            }
        }

        return [
            'unit_id' => $unit->id,
            'unit_name' => $unit_name,
            'periode_id' => $periode->id,
            'tahun_audit' => $periode->tahun_audit,
            'level' => $unit->jenis_unit,
            'penilaian_panduan_label' => $penilaian_panduan_label,
            'penilaian_panduan_options' => $penilaian_panduan_options,
            'child_unit_options' => $child_unit_options,
        ];
    }

    public function getListMapping(int $idUnit, int $idPeriode, int $idPenilaianPanduan, bool $isTree = false)
    {
        return DB::select("SELECT
            pm.id,
            pm.pertanyaan_penilaian as indikator,
            pm.info_level,
            pm.apakah_data_default,
            pm.id_parent,
            pm.info_left
        FROM
            spmi.mapping_penilaian_matriks mpm
        join spmi.penilaian_matriks pm on
            mpm.id_penilaian_matriks = pm.id
            and pm.id_penilaian_panduan = :id_penilaian_panduan
            and pm.waktu_dihapus is null
        where
            mpm.id_unit = :id_unit
	    and mpm.id_audit_periode = :id_audit_periode
        group by pm.id
        order by pm.info_left", [
            'id_unit' => $idUnit,
            'id_audit_periode' => $idPeriode,
            'id_penilaian_panduan' => $idPenilaianPanduan,
        ]);
    }

    public function syncMatriksPengisian($matriks, $penilaianPanduanId, $auditPeriodId, $unitId, $withSave = false)
    {
        $penilaianLKED = DB::select("SELECT
            pp.id as lk_id,
            pp.kode_pengisian_panduan as lk_kode,
            pp.nama_singkat as lk_nama,
            pp3.id as ed_id,
            pp3.kode_pengisian_panduan as ed_kode,
            pp3.nama_singkat as ed_nama
            from spmi.pengisian_panduan pp
            join spmi.penilaian_panduan pp2 on pp2.id_laporan_kinerja = pp.id
            left join spmi.pengisian_panduan pp3 on pp.id_pengisian_panduan = pp3.id
            where pp2.id = :penilaian_panduan_id
        ", ['penilaian_panduan_id' => $penilaianPanduanId]);

        if (empty($penilaianLKED)) {
            return [false, 'Panduan Penilaian tidak ditemukan.'];
        }

        $penilaianLKED = reset($penilaianLKED);

        $ref = PenilaianMatriksReferensi::whereIn('id_penilaian_matriks', $matriks)->get();
        $refLK = $ref->where('jenis_referensi', PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT)->pluck('id_butir_referensi')->toArray();
        $refED = $ref->where('jenis_referensi', PenilaianMatriks::REFERENCE_SELF_EVALUATION)->pluck('id_butir_referensi')->toArray();

        $matriksLK = IndikatorLaporanKinerja::whereIn('id', $refLK)->get();
        $matriksED = IndikatorEvaluasiDiri::whereIn('id', $refED)->get();

        $mappingLK = DB::table('spmi.mapping_lk as mlk')
            ->join('spmi.indikator_laporan_kinerja as ilk', 'ilk.id', '=', 'mlk.id_indikator_laporan_kinerja')
            ->join('spmi.pengisian_panduan as pp', 'pp.id', '=', 'ilk.id_pengisian_panduan')
            ->where('mlk.id_audit_periode', $auditPeriodId)
            ->where('pp.id', $penilaianLKED->lk_id)
            ->when(is_array($unitId), function ($query) use ($unitId) {
                $query->whereIn('mlk.id_unit', $unitId);
            }, function ($query) use ($unitId) {
                $query->where('mlk.id_unit', $unitId);
            })
            ->get();

        $mappingED = DB::table('spmi.mapping_led as med')
            ->join('spmi.indikator_evaluasi_diri as ied', 'ied.id', '=', 'med.id_indikator_evaluasi_diri')
            ->join('spmi.pengisian_panduan as pp', 'pp.id', '=', 'ied.id_pengisian_panduan')
            ->where('med.id_audit_periode', $auditPeriodId)
            ->where('pp.id', $penilaianLKED->ed_id)
            ->when(is_array($unitId), function ($query) use ($unitId) {
                $query->whereIn('med.id_unit', $unitId);
            }, function ($query) use ($unitId) {
                $query->where('med.id_unit', $unitId);
            })
            ->get();

        $isDiff = false;
        $diffLK = $matriksLK->whereNotIn('id', $mappingLK->pluck('id_indikator_laporan_kinerja'));
        $diffED = $matriksED->whereNotIn('id', $mappingED->pluck('id_indikator_evaluasi_diri'));
        if ($diffLK->isNotEmpty() || $diffED->isNotEmpty()) {
            $isDiff = true;
        }

        if ($isDiff && $withSave) {
            if ($diffLK->isNotEmpty()) {
                $mergeLKMatrices = $mappingLK
                    ->pluck('id_indikator_laporan_kinerja')
                    ->merge($diffLK->pluck('id'))
                    ->unique()
                    ->toArray();

                $mappingLK = new MappingLKManagementService();
                $mappingLK->store([
                    'mapping' => $mergeLKMatrices,
                    'id_pengisian_panduan' => null,
                    'id_audit_periode' => $auditPeriodId,
                    'id_unit' => $unitId,
                ]);
            }

            if ($diffED->isNotEmpty()) {
                $mergeEDMatrices = $mappingED
                    ->pluck('id_indikator_evaluasi_diri')
                    ->merge($diffED->pluck('id'))
                    ->unique()
                    ->toArray();

                $mappingED = new MappingLEDManagementService();
                $mappingED->store([
                    'mapping' => $mergeEDMatrices,
                    'id_pengisian_panduan' => null,
                    'id_audit_periode' => $auditPeriodId,
                    'id_unit' => $unitId,
                ]);
            }

            return [true, null];
        }

        if ($isDiff && !$withSave) {
            return [false, 'Terdapat perubahan pada butir referensi. Silakan simpan untuk memperbarui mapping.'];
        }

        return [true, null];
    }
}
