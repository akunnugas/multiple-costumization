<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\IndikatorKolom;
use Modules\SPMI\Services\IndikatorKolomManagementService;

class IndikatorKolomController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorKolomManagementService $service)
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
        $header = [
            ['field' => 'nama', 'is_tree_view' => true],
			['field' => 'jenis_kolom'],
			['field' => 'posisi_kolom'],
			['field' => 'apakah_terlihat', 'searchable' => false],
        ];


        $viewData['sidebar'] = Menu::indicatorSidebar($idIndicator);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');
        $viewData['isDetailV2'] = true;

        $this->service->setIndicatorId($idIndicator);

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(int $idIndicator)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-kolom", includes: true);
        return WebController::create($this->defineFormFields($idIndicator), IndikatorKolom::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, int $idIndicator)
    {
        $request['id_indikator_laporan_kinerja'] = $idIndicator;

        return WebController::store($this->service, $request, $this->defineFormFields($idIndicator), IndikatorKolom::class);
    }

    /**
     * Show the specified resource.
     * @param int $idIndicator
     * @param int $id
     * @return Renderable
     */
    public function show(int $idIndicator, int $id)
    {
        $cards = $this->defineFormFields($idIndicator);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-kolom", includes: true);
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, IndikatorKolom::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(int $idIndicator, int $id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-kolom", includes: true);
        return WebController::edit($this->service, $id, $this->defineFormFields($idIndicator), IndikatorKolom::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, int $idIndicator, int $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields($idIndicator), IndikatorKolom::class);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(int $idIndicator, int $id)
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
    private function defineFormFields(int $id)
    {
        $optParent = IndikatorKolom::optionsIndicatorColumn($id);
        return [
            ['field' => 'nama'],
            ['field' => 'id_parent', 'options' => $optParent],
            ['field' => 'jenis_form'],
			['field' => 'jenis_kolom'],
			['field' => 'posisi_kolom'],
            ['field' => 'colspan', 'type' => 'number'],
            ['field' => 'rowspan', 'type' => 'number'],
			['field' => 'apakah_terlihat', 'control' => 'switch'],
            ['field' => 'id_indikator_laporan_kinerja', 'type' => 'hidden']
        ];
    }
}
