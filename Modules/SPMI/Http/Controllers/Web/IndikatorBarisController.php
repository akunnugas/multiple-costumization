<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\IndikatorBaris;
use Modules\SPMI\Services\IndikatorBarisManagementService;

class IndikatorBarisController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorBarisManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request, int $idIndicator)
    {
        $header = [
            ['field' => 'nama', "is_tree_view" => true],
            ['field' => 'jenis_penomoran'],
        ];

        $viewData['sidebar'] = Menu::indicatorSidebar($idIndicator);
        $viewData['isDetailV2'] = true;
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');

        $this->service->setIndicatorId($idIndicator);

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(int $idIndicator)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-baris", includes: true);
        return WebController::create($this->defineFormFields($idIndicator), IndikatorBaris::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, int $idIndicator)
    {
        $request['id_indikator_laporan_kinerja'] = $idIndicator;

        return WebController::store($this->service, $request, $this->defineFormFields($idIndicator), IndikatorBaris::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(int $idIndicator, int $id)
    {
        $cards = $this->defineFormFields($idIndicator);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-baris", includes: true);
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, IndikatorBaris::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(int $idIndicator, int $id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-baris", includes: true);
        return WebController::edit($this->service, $id, $this->defineFormFields($idIndicator), IndikatorBaris::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, int $idIndicator, int $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields($idIndicator), IndikatorBaris::class);
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
        $optParent = IndikatorBaris::optionsIndikatorBaris($id);

        return [
            ['field' => 'nama'],
            ['field' => 'id_parent', 'options' => $optParent],
            ['field' => 'jenis_penomoran'],
            ['field' => 'row_range_from'],
            ['field' => 'row_range_to'],
            ['field' => 'id_indikator_laporan_kinerja', 'type' => 'hidden']
        ];
    }
}
