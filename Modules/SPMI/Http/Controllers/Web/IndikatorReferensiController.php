<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\IndikatorReferensi;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Services\IndikatorReferensiManagementService;

class IndikatorReferensiController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorReferensiManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param int $idIndicator
     * @return Renderable
     */
    public function index(Request $request, int $idIndicator)
    {

        // get filter
        $filterHeader = $request->query('filter');
        if (!empty($filterHeader['id_pengisian_panduan'])) {
            $idPengisianPanduan = $filterHeader['id_pengisian_panduan'];
            $firstIdPengisianPanduan = $idPengisianPanduan;
        } else {
            $idPengisianPanduan = IndikatorEvaluasiDiri::getPengisianPanduanByID($idIndicator);
            $firstIdPengisianPanduan = PengisianPanduan::findFirstRefrence($idPengisianPanduan)?->id;
        }

        $header = [
            ['field' => 'id_indikator_laporan_kinerja', 'options' => IndikatorLaporanKinerja::getByPengisianPanduan($firstIdPengisianPanduan)],
        ];

        $viewData['sidebar'] = Menu::indicatorSelfEvaluationSidebar($idIndicator);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-evaluasi-diri');
        $viewData['isDetailV2'] = true;
        $filter = [
            'id_indikator_evaluasi_diri' => [
                'selected' => $idIndicator,
            ],
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData, model: IndikatorReferensi::class, isReference: true);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), IndikatorReferensi::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param int $idIndicator
     * @return Renderable
     */
    public function store(Request $request, int $idIndicator)
    {
        // get filter
        $filterHeader = $request->query('filter');
        if (!empty($filterHeader['id_pengisian_panduan'])) {
            $idPengisianPanduan = $filterHeader['id_pengisian_panduan'];
        } else {
            $idPengisianPanduan = IndikatorLaporanKinerja::getPengisianPanduanByID($request->id_indikator_laporan_kinerja);
        }

        // merge request
        $request['id_indikator_evaluasi_diri'] = $idIndicator;
        $request['id_pengisian_panduan'] = $idPengisianPanduan;

        return WebController::store($this->service, $request, $this->defineFormFields(), IndikatorReferensi::class, isReference: true);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, IndikatorReferensi::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), IndikatorReferensi::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $idIndicator
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, int $idIndicator, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), IndikatorReferensi::class, isReference: true);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $idIndicator
     * @param int $id
     * @return Renderable
     */
    public function destroy(int $idIndicator, $id)
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

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [];
    }
}
