<?php

namespace Modules\SPMI\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ManagementService;
use Modules\Core\Services\Service;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\Core\Helpers\Pagination;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\MappingLED;

class MappingLEDManagementService extends Service
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
     * Buat data baru.
     *
     * @param array $data
     *
     */
    public function store(array $data)
    {
        $mappings = $data['mapping'] ?? [];

        // Hapus mapping sebelumnya
        $listIdEvaluasiDiri = IndikatorEvaluasiDiri::where('id_pengisian_panduan', $data['id_pengisian_panduan'])
            ->pluck('id')
            ->toArray();
        MappingLED::where('id_audit_periode', $data['id_audit_periode'])
            ->whereIn('id_indikator_evaluasi_diri', $listIdEvaluasiDiri)
            ->when(is_array($data['id_unit']), function ($query) use ($data) {
                $query->whereIn('id_unit', $data['id_unit']);
            }, function ($query) use ($data) {
                $query->where('id_unit', $data['id_unit']);
            })
            ->delete();

        foreach ($mappings as $id) {
            try {
                // update mapping baru
                if (isset($data['id_unit']) && is_array($data['id_unit'])) {
                    foreach ($data['id_unit'] as $idUnit) {
                        MappingLED::updateOrCreate(
                            [
                                'id_indikator_evaluasi_diri' => $id,
                                'id_audit_periode' => $data['id_audit_periode'],
                                'id_unit' => $idUnit,
                            ],
                            []
                        );
                    }
                } else {
                    MappingLED::updateOrCreate(
                        [
                            'id_indikator_evaluasi_diri' => $id,
                            'id_audit_periode' => $data['id_audit_periode'],
                            'id_unit' => $data['id_unit'],
                        ],
                        []
                    );
                }
            } catch (\Exception $e) {
                return $e;
            }
        }

        return 1;
    }
}
