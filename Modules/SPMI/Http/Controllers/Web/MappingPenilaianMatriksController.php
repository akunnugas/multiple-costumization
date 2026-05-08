<?php
// ! TEMP
namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Services\MappingPenilaianMatriksManagementService;

class MappingPenilaianMatriksController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private MappingPenilaianMatriksManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'nama_unit', 'label' => 'Unit Kerja'],
            ['field' => 'nama_penilaian_panduan', 'label' => 'Panduan Penilaian'],
            ['field' => 'status', 'label' => 'Status Mapping'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optAuditPeriode = AuditPeriode::options();

        $idAuditPeriode = $request->input('id_audit_periode') ?? session('filter_mapping_penilaian_matriks_audit_periode') ?? array_key_first($optAuditPeriode);

        if (!empty($idAuditPeriode)) {
            session(['filter_mapping_penilaian_matriks_audit_periode' => $idAuditPeriode]);
        }

        if (!isset($optAuditPeriode[$idAuditPeriode])) {
            $idAuditPeriode = array_key_first($optAuditPeriode);
            session(['filter_mapping_penilaian_matriks_audit_periode' => $idAuditPeriode]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Tahun Audit',
                'selected' => $idAuditPeriode
            ],
        ];

        if (request()->has('search')) {
            request()->merge(['search' => null]);
        }

        return WebController::index($this->service, $request, $header, $filter, $viewData, isReference: true)
            ->withPermission($request->permission);
    }

    public function show($unit_id, $period_id, Request $request)
    {
        $cards = [];

        $viewData['sidebar'] = Menu::mappingPenilaianMatriksSidebar(unitId: $unit_id, periodId: $period_id);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.mapping-indikator-butir');
        $viewData['isDetailV2'] = true;

        $rawData['unit_id'] = $unit_id;
        $rawData['period_id'] = $period_id;

        $rawData['detail'] = $this->service->detail($unit_id, $period_id);

        $selectedIdUnit = $unit_id;
        if ($rawData['detail']['child_unit_options']) {
            $selectedIdUnit = $request->input('id_unit') ?? array_key_first($rawData['detail']['child_unit_options'] ?? []);
        }
        $rawData['detail']['selected_child_unit'] = $selectedIdUnit;

        if (in_array($rawData['detail']['level'], [UnitKerja::UNIVERSITY, UnitKerja::FACULTY, UnitKerja::MAJOR])) {
            $getPanduanList = $this->service->detail($selectedIdUnit, $period_id);
            $rawData['detail']['penilaian_panduan_options'] = $getPanduanList['penilaian_panduan_options'];
        }

        $selectedIdPenilaianPanduan = $request->input('id_penilaian_panduan') ?? array_key_first($rawData['detail']['penilaian_panduan_options'] ?? []);
        $rawData['detail']['selected_penilaian_panduan'] = $selectedIdPenilaianPanduan;

        $rawData['indicator_list'] = [];
        $rawData['title'] = 'Mapping Matriks Penilaian';

        $rawData['indicator_list'] = [];
        if ($selectedIdPenilaianPanduan && $selectedIdUnit) {
            $rawData['indicator_list'] = $this->service->getListMapping($selectedIdUnit, $period_id, $selectedIdPenilaianPanduan, true);
        }

        return WebController::show(
            service: $this->service,
            id: $unit_id,
            cards: $cards,
            viewData: $viewData,
            data: $rawData,
        );
    }

    public function store(Request $request)
    {
        $optAuditPeriode = AuditPeriode::options();
        $optUnitKerja = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI]);

        $idAuditPeriode = $request->input('mk_id_audit_periode') ?? session('filter_mapping_penilaian_matriks_audit_periode') ?? array_key_first($optAuditPeriode);
        $idUnitKerja = $request->input('mk_id_unit') ?? session('filter_mapping_penilaian_matriks_unit_kerja') ?? array_key_first($optUnitKerja);
        $idPenilaianPanduan = $request->input('pm_id_penilaian_panduan') ?? session('filter_mapping_penilaian_panduan') ?? null;

        $data['id_audit_periode'] = $idAuditPeriode;
        $data['id_unit'] = $idUnitKerja;
        $data['id_penilaian_panduan'] = $idPenilaianPanduan;

        foreach ($request->data as $item) {
            if (isset($item['selected']) && !empty($item['selected'])) {
                $data['mapping'][] = $item;
            }
        }

        $status = $this->service->store($data);

        return to_route('spmi.mapping-matriks-penilaian.index')->with(($status == 1 ? 'success' : 'error'), ($status == 1 ? 'Mapping berhasil disimpan' : $status));
    }
}
