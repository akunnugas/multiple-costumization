<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\TargetIndikatorManagementService;
use Modules\SPMI\Services\TinjauanTemuanManagementService;

class TargetIndikatorController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private TargetIndikatorManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'periode_audit', 'label' => 'Periode AMI'],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'nama_unit', 'label' => 'Unit Kerja / Program Studi', 'order' => 'asc'],
            ['field' => 'nama_pengisian_panduan'],
            ['field' => 'nama_penilaian_panduan'],
            ['field' => 'status_pengisian_target', 'label' => 'Status Penetapan Target', 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => 'target_indikator', 'searchable' => false]
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_target_indikator_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_target_indikator_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_target_indikator_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_target_indikator_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_target_indikator_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_target_indikator_unit_kerja' => $idUnitKerja]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode
            ],
            'id_jenjang_pendidikan' => [
                'options' => $optJenjangPendidikan,
                'label' => 'Jenjang Pendidikan',
                'selected' => $idJenjangPendidikan,
                'is_empty' => true
            ],
            'id_unit_kerja' => [
                'options' => $optUnitKerja,
                'label' => 'Unit Kerja',
                'selected' => $idUnitKerja,
                'is_empty' => true
            ],
        ];

        $invalidParam = !WebRequest::validateId($request->filter['id_audit_periode'] ?? null);
        if (isset($request->filter['id_audit_periode']) && $invalidParam) {
            $request->merge([
                'filter' => [
                    'id_audit_periode' => null
                ]
            ]);
        }

        $viewData['title'] = 'Atur Target Capaian';
        $viewData['subtitle'] = '';
        $viewData['emptyState'] = [
            'title' => 'Buat Target Capaian Program Studi',
            'subtitle' => 'Tentukan target yang ingin dicapai setiap periode untuk program studi yang Anda miliki'
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id) {}

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return WebController::destroy($this->service, $id);
    }

    /**
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->service, $request);
    }


    public function copy(Request $request)
    {
        $oldIdAuditPeriode = $request->input('old_id_audit_periode');
        $newIdAuditPeriode = $request->input('new_id_audit_periode');
        $idUnitKerja = $request->input('id_unit_kerja');

        if (empty($oldIdAuditPeriode) || empty($idUnitKerja) || empty($newIdAuditPeriode)) {
            return redirect()->back()->withError("Salin data gagal, Silakan lengkapi semua isian terlebih dahulu.");
        }

        $copyData = $this->service->copyData($oldIdAuditPeriode, $newIdAuditPeriode, $idUnitKerja);

        if (Error::isError($copyData)) {
            return $copyData->redirectBack();
        }

        if ($request->has('with_rtm')) {
            $tinjauanManagementService = new TinjauanTemuanManagementService();
            $dataRTM = $tinjauanManagementService->showByAuditPeriode($newIdAuditPeriode, $idUnitKerja);
            if ($dataRTM) {
                $nextTargetUrl = $tinjauanManagementService->syncNextTarget($dataRTM->id);
                $err = Error::isError($nextTargetUrl);
                if ($err) {
                    return redirect()->back()->withError("Terjadi kesalahan saat menyalin data RTM");
                }
            }
        }

        return redirect()->back()->withSuccess($copyData);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        $years = [];
        foreach (range(now()->year - 10, now()->year) as $year) {
            $years[$year] = $year;
        }

        return [
            [
                'field' => 'period',
                'control' => 'select',
                'options' => $years,
                'selected' => now()->year
            ],
            ['field' => 'nama_lembaga_akreditasi_id'],
            ['field' => 'study_degree_id'],
            ['field' => 'penilaian_panduan_id'],
            ['field' => 'id_unit'],
            [
                'field' => 'is_active',
                'control' => 'select',
                'options' => [0 => 'Tidak Aktif', 1 => 'Aktif'],
                'selected' => 1
            ],
            ['field' => 'description', 'control' => 'textarea'],
        ];
    }
}
