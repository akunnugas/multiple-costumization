<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\IndikatorBobotManagementService;

class SettingIndikatorBobotController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorBobotManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'tahun_audit', 'readonly' => true, 'sortable' => false],
            ['field' => 'nama_kategori_indikator', 'readonly' => true],
            ['field' => 'status_bobot', 'label' => 'Status', 'searchable' => false, 'readonly' => true],
            ['field' => 'persentase', 'type' => 'number', 'min' => 0, 'max' => 100],
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optUnitKerja = UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_setting_bobot_indikator_periode') ?? array_key_first($optAuditPeriode);
        $idUnitKerja = $request->filter['id_unit'] ?? session('filter_setting_bobot_indikator_unit_kerja') ?? array_key_first($optUnitKerja);
        $idPenilaianPanduan = $request->filter['id_penilaian_panduan'] ?? session('filter_setting_bobot_indikator_penilaian_panduan') ?? null;

        $penilaianPanduanIds = MappingPenilaianMatriks::join('spmi.penilaian_matriks', 'spmi.mapping_penilaian_matriks.id_penilaian_matriks', '=', 'spmi.penilaian_matriks.id')
            ->where('spmi.mapping_penilaian_matriks.id_audit_periode', $idAuditPeriode)
            ->where('spmi.mapping_penilaian_matriks.id_unit', $idUnitKerja)
            ->pluck('id_penilaian_panduan')
            ->toArray();
        $optPenilaianPanduan = PenilaianPanduan::optionsByIds($penilaianPanduanIds);
        if (is_null($idPenilaianPanduan)) {
            $idPenilaianPanduan = array_key_first($optPenilaianPanduan);
        } else {
            if (!isset($optPenilaianPanduan[$idPenilaianPanduan])) {
                $idPenilaianPanduan = null;
                session()->forget('filter_setting_bobot_indikator_penilaian_panduan');
            }
        }

        if (!empty($idAuditPeriode)) {
            session(['filter_setting_bobot_indikator_periode' => $idAuditPeriode]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_setting_bobot_indikator_unit_kerja' => $idUnitKerja]);
        }
        if (!empty($idPenilaianPanduan)) {
            session(['filter_setting_bobot_indikator_penilaian_panduan' => $idPenilaianPanduan]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode,
            ],
            'id_unit' => [
                'options' => $optUnitKerja,
                'label' => 'Unit Kerja',
                'selected' => $idUnitKerja,
            ],
            'id_penilaian_panduan' => [
                'options' => $optPenilaianPanduan,
                'label' => 'Panduan Penilaian',
                'selected' => $idPenilaianPanduan,
            ],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $view = WebController::index($this->service, $request, $header, $filter, viewData: $viewData, isReference: true);

        // Ambil id periode dari filter
        $periodId = $view['filter']['id_audit_periode']['selected'];
        $unitId = $view['filter']['id_unit']['selected'];
        $idPenilaianPanduan = $view['filter']['id_penilaian_panduan']['selected'];
        $indicatorPercentage = AuditPeriode::countIndikatorBobot($periodId, $unitId, $idPenilaianPanduan);

        return $view->withIndikatorBobot($indicatorPercentage)
            ->withPermission($request->permission);
    }

    /**
     * Update setting.
     * @param Request $request
     * @return Renderable
     */
    public function updateSetting(Request $request)
    {
        if (isset($id) && !filter_var($id, FILTER_VALIDATE_INT)) {
            abort(404);
        }

        $data = $request->data;

        $updated = $this->service->setPercentages($data);

        if (Error::isError($updated)) {
            return $updated->redirectBack();
        }

        $url = Page::indexURL();

        return redirect($url)->withSuccess('Bobot indikator audit berhasil di update');
    }
}
