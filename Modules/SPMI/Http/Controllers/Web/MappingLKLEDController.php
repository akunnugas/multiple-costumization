<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\UI;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\IndikatorEvaluasiDiriManagementService;
use Modules\SPMI\Services\IndikatorLaporanKinerjaManagementService;
use Modules\SPMI\Services\MappingLKLEDManagementService;

class MappingLKLEDController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private MappingLKLEDManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'nama_unit', 'label' => 'Unit Kerja'],
            ['field' => 'nama_pengisian_panduan', 'label' => 'Panduan Pengisian'],
            ['field' => 'status', 'label' => 'Status Mapping'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optAuditPeriode = AuditPeriode::options();

        $idAuditPeriode = $request->input('id_audit_periode') ?? session('filter_mapping_lkled_audit_periode') ?? array_key_first($optAuditPeriode);

        if (!empty($idAuditPeriode)) {
            session(['filter_mapping_lkled_audit_periode' => $idAuditPeriode]);
        }

        if (!isset($optAuditPeriode[$idAuditPeriode])) {
            $idAuditPeriode = array_key_first($optAuditPeriode);
            session(['filter_mapping_lkled_audit_periode' => $idAuditPeriode]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode
            ],
        ];

        if (request()->has('search')) {
            request()->merge(['search' => null]);
        }

        $viewData['title'] = 'Mapping LK & ED';
        $viewData['subtitle'] = '';

        return WebController::index($this->service, $request, $header, $filter, $viewData, isReference: true)
            ->withPermission($request->permission);
    }

    public function show($unit_id, $period_id, Request $request) {
        $cards = [];

        $viewData['sidebar'] = Menu::mappingIndikatorButirSidebar(unitId: $unit_id, periodId: $period_id);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.mapping-indikator-butir');
        $viewData['isDetailV2'] = true;

        $rawData['unit_id'] = $unit_id;
        $rawData['period_id'] = $period_id;
        $rawData['type'] = AkreditasiBuku::PERFORMANCE_REPORT;
        $rawData ['title'] = 'Mapping Laporan Kinerja';
        if ($request->has('tab') && $request->tab == AkreditasiBuku::SELF_EVALUATION) {
            $rawData['type'] = AkreditasiBuku::SELF_EVALUATION;
            $rawData ['title'] = 'Mapping Komponen Evaluasi Diri';
        }

        $rawData['detail'] = $this->service->detail($unit_id, $period_id, $rawData['type']);

        $selectedIdUnit = $unit_id;
        if ($rawData['detail']['child_unit_options']) {
            $selectedIdUnit = $request->input('id_unit') ?? array_key_first($rawData['detail']['child_unit_options'] ?? []);
        }
        $rawData['detail']['selected_child_unit'] = $selectedIdUnit;

        if (in_array($rawData['detail']['level'], [UnitKerja::UNIVERSITY, UnitKerja::FACULTY, UnitKerja::MAJOR])) {
            $getPanduanList = $this->service->detail($selectedIdUnit, $period_id, $rawData['type']);
            $rawData['detail']['pengisian_panduan_options'] = $getPanduanList['pengisian_panduan_options'];
        }

        $selectedIdPengisianPanduan = $request->input('id_pengisian_panduan') ?? array_key_first($rawData['detail']['pengisian_panduan_options'] ?? []);
        $rawData['detail']['selected_pengisian_panduan'] = $selectedIdPengisianPanduan;

        $rawData['indicator_list'] = [];
        if ($rawData['type'] == AkreditasiBuku::PERFORMANCE_REPORT && $selectedIdPengisianPanduan && $selectedIdUnit) {
            $indicator_list = new IndikatorLaporanKinerjaManagementService();
            $rawData['indicator_list'] =  $indicator_list->showAllIndicatorsByPengisianPanduan($selectedIdPengisianPanduan, $period_id, $selectedIdUnit, true);
        }

        if ($rawData['type'] == AkreditasiBuku::SELF_EVALUATION && $selectedIdPengisianPanduan && $selectedIdUnit) {
            $indicator_list = new IndikatorEvaluasiDiriManagementService();
            $rawData['indicator_list'] = $indicator_list->showAllIndicatorsByPengisianPanduan($selectedIdPengisianPanduan, $period_id, $selectedIdUnit, true);
        }

        return WebController::show(
            service: $this->service,
            id: $unit_id, cards: $cards,
            model: IndikatorEvaluasiDiri::class,
            viewData: $viewData,
            data: $rawData,
        );
    }

    /**
     * store mapping.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $optAuditPeriode = AuditPeriode::options();
        $optUnitKerja = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM]);
        $optPengisianPanduan = PengisianPanduan::getListIndicatorPerformanceReport();

        $idAuditPeriode = $request->input('mk_id_audit_periode') ?? session('filter_mapping_lkled_audit_periode') ?? array_key_first($optAuditPeriode);
        $idUnitKerja = $request->input('mk_id_unit') ?? session('filter_mapping_lkled_unit_kerja') ?? array_key_first($optUnitKerja);
        $idPengisianPanduan = $request->input('pm_id_pengisian_panduan') ?? session('filter_mapping_lkled_pengisian_panduan') ?? array_key_first($optPengisianPanduan);

        $data['id_pengisian_panduan'] = $idPengisianPanduan;
        $data['id_audit_periode'] = $idAuditPeriode;
        $data['id_unit'] = $idUnitKerja;

        foreach ($request->data as $item) {
            if (isset($item['selected']) && !empty($item['selected'])) {
                $data['mapping'][] = $item;
            }
        }

        $status = $this->service->store($data);

        return to_route('spmi.mapping-laporan-kinerja.index')->with(($status == 1 ? 'success' : 'error'), ($status == 1 ? 'Mapping berhasil disimpan' : $status));
    }
}
