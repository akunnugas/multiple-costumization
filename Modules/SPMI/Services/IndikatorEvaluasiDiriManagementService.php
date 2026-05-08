<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\DataPengisianLED;
use Modules\SPMI\Models\DokumenPendukungPengisianLed;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\MappingLED;
use Modules\SPMI\Models\PenilaianMatriksReferensi;

class IndikatorEvaluasiDiriManagementService
{
    /**
     * @var IndikatorEvaluasiDiri
     */
    protected $model = IndikatorEvaluasiDiri::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new IndikatorEvaluasiDiri;
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

        $table = $this->model->getTable();
        $sql = "SELECT * FROM $table led";

        $defaultFilter = "led.waktu_dihapus is null";

        if (!empty($order) && $order['field'] == 'nomor_indikator') {
            $order = ['field' => 'led.nomor_indikator', 'direction' => 'asc', 'desc' => false];
        }

        if (isset($filter['apakah_data_default'])) {
            if ($filter['apakah_data_default']['selected'] === 'true') {
                $defaultFilter .= " AND led.apakah_data_default = true";
            } elseif ($filter['apakah_data_default']['selected'] === 'false') {
                $defaultFilter .= " AND led.apakah_data_default = false";
            }
        }
        
        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return IndikatorEvaluasiDiri
     */
    public function show(int $id, $withAccess = false): IndikatorEvaluasiDiri
    {
        $model = $this->model->findOrFail($id);

        if (!$withAccess) {
            $this->setHakAksesIKT($model);
        }

        return $model;
    }

    /**
     * Buat data baru.
     *
     * @param array $data
     *
     * @return IndikatorEvaluasiDiri
     */
    public function store(array $data): IndikatorEvaluasiDiri | Error
    {
        try {
            $new = $this->model->create($data);

            if (isset($data['program_studi']) && isset($data['periode_ami'])) {
                $mappingED = [];
                foreach ($data['periode_ami'] as $periode) {
                    foreach ($data['program_studi'] as $unit) {
                        $mappingED[] = [
                            'id_indikator_evaluasi_diri' => $new->id,
                            'id_audit_periode' => $periode,
                            'id_unit' => $unit,
                        ];
                    }
                }

                MappingLED::insert($mappingED);
            }

            return $new;
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     *
     * @return IndikatorEvaluasiDiri
     */
    public function update(array $data, int $id): IndikatorEvaluasiDiri | Error
    {
        $model = $this->model->findOrFail($id);

        if ($data['id_parent'] == '') {
            $data['id_parent'] = null;
        }

        try {
            $model->update($data);

            if (isset($data['program_studi']) && isset($data['periode_ami'])) {
                MappingLED::where('id_indikator_evaluasi_diri', $id)->delete();
                $mappingED = [];
                foreach ($data['periode_ami'] as $periode) {
                    foreach ($data['program_studi'] as $unit) {
                        $mappingED[] = [
                            'id_indikator_evaluasi_diri' => $id,
                            'id_audit_periode' => $periode,
                            'id_unit' => $unit,
                        ];
                    }
                }

                MappingLED::insert($mappingED);
            }
        } catch (\Exception $e) {
            return new Error(exception: $e);
        }

        return $model;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     *
     * @return void
     */
    public function destroy(int $id): Error|bool
    {
        if ($this->checkReference($id)) {
            return new Error('Data tidak bisa dihapus karena sudah digunakan sebagai referensi.');
        }

        $model = $this->model->findOrFail($id);

        try {
            $model->destroy($model->id);
        } catch (\Throwable) {
            return new Error('Gagal menghapus data');
        }

        return true;
    }

    protected function checkReference($id)
    {
        $isParent = $this->model->where('id_parent', $id)->exists();
        $isReferenceDataPengisianLed = DataPengisianLED::where('id_indikator_evaluasi_diri', $id)->exists();
        $isReferenceDokumenPengisianLed = DokumenPendukungPengisianLed::where('id_indikator_evaluasi_diri', $id)->exists();
        $isReferencePenilaianMatriks = PenilaianMatriksReferensi::where('jenis_referensi', AkreditasiBuku::SELF_EVALUATION)
            ->where('id_butir_referensi', $id)->exists();
        $isReferenceMappingLED = MappingLED::where('id_indikator_evaluasi_diri', $id)->exists();

        return $isReferenceDataPengisianLed || $isReferenceDokumenPengisianLed || $isReferencePenilaianMatriks || $isParent || $isReferenceMappingLED;
    }

    /**
     * Hapus beberapa data berdasarkan id.
     *
     * @param $ids
     * @return Error|null
     */
    public function destroySome($ids)
    {
        return ManagementService::create($this->model)->destroySome($ids);
    }

    /**
     * Get all indicator performance report by filling guide
     *
     * @return mixed
     */
    public function showAllIndicatorsByPengisianPanduan(int $idPanduanPengisian, int $idAuditPeriode, int $idUnit, bool $isTree = false, $link = null)
    {
        $data = DB::table('spmi.mapping_led as mld')
            ->where('mld.id_audit_periode', $idAuditPeriode)
            ->where('mld.id_unit', $idUnit)
            ->join('spmi.indikator_evaluasi_diri as ild', function ($join) use ($idPanduanPengisian) {
                $join->on('ild.id', '=', 'mld.id_indikator_evaluasi_diri')
                    ->where('ild.id_pengisian_panduan', '=', $idPanduanPengisian);
            })
            ->where('ild.waktu_dihapus', null)
            ->orderBy('ild.nomor_indikator', 'asc')
            ->orderBy('ild.info_left', 'asc')
            ->get([
                'ild.id',
                'ild.id_parent',
                'ild.nomor_indikator',
                'ild.apakah_parent',
                'ild.nama_indikator_evaluasi_diri',
                'ild.info_level',
                'ild.info_left',
                'ild.info_right',
                'ild.apakah_data_default',
            ]);

        if ($isTree) {
            $tree = [];
            foreach ($data as $row) {
                $v = (array) $row;
                $id = $v['id'];

                $tree[$id] = $v + [
                    'link' => $link . $id,
                    'children' => [],
                ];
            }

            foreach ($tree as $id => &$node) {
                $pid = $node['id_parent'];

                if ($pid !== null && isset($tree[$pid])) {
                    $tree[$pid]['children'][] = &$node;
                } else {
                    if ($pid !== null && isset($node['apakah_parent']) && $node['apakah_parent'] === false) {
                        $node['id_parent'] = null;
                    }
                }
            }
            unset($node);

            $roots = array_filter($tree, function ($node) {
                return $node['id_parent'] === null;
            });

            $sortChildren = function (&$nodes) use (&$sortChildren) {
                usort($nodes, fn($a, $b) => $a['info_left'] <=> $b['info_left']);

                foreach ($nodes as &$n) {
                    if (!empty($n['children'])) {
                        usort($n['children'], fn($a, $b) => $a['info_left'] <=> $b['info_left']);
                        $sortChildren($n['children']);
                    }
                }
            };
            $sortChildren($roots);

            $flatten = function ($nodes) use (&$flatten) {
                $result = [];
                foreach ($nodes as $node) {
                    $children = $node['children'] ?? [];
                    $node['children'] = [];
                    $result[] = $node;
                    if (!empty($children)) {
                        $result = array_merge($result, $flatten($children));
                    }
                }
                return $result;
            };

            $data = collect($flatten($roots));
        }

        return $data;
    }

    /**
     * Generator Table Indicator Performance Report
     */
    public function generateTable(int $id, $cond = [], $records = [], $isEditAll = false, $isPreview = false)
    {
        // indicator performance report information
        $indicator = $this->show($id, withAccess: true);

        $cond['is_comment'] = $indicator->is_comment;

        $output = '<table class="table table-bordered table-hover table-striped">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th class="text-center">Uraian</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        $output .= '<tr>';
        $output .= '<td>';
        if ($isEditAll) {
            $output .= '<div class="form-control">';
            $output .= '<div class="text-editor" id="text-editor">';
            if (!empty($records['data_pengisian_led']))
                $output .= $records['data_pengisian_led'];
            $output .= '</div>';
            $output .= '<input type="hidden" id="uraian_input" name="uraian" value"">';
            $output .= '</div>';
        } else {
            $output .= '<div class="ql-editor" style="height:auto;">';
            if (!empty($records['data_pengisian_led'])) {
                $output .= $records['data_pengisian_led'];
            } else {
                $output .= '<style> .ql-editor { text-align: center; } </style> Tidak ada data';
            }
            $output .= '</div>';
        }

        $output .= '</td>';
        $output .= '</tr>';

        $output .= '</tbody>';
        $output .= '</table>';

        return $output;
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
