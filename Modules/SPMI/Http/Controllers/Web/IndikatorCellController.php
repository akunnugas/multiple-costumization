<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\IndikatorCell;
use Modules\SPMI\Services\IndikatorCellManagementService;

class IndikatorCellController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorCellManagementService $service)
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
            ['field' => 'column_to', 'casting' => true],
            ['field' => 'row_to', 'casting' => true],
            ['field' => 'jenis_cell'],
            ['field' => 'nama'],
            ['field' => 'dapat_dilihat', 'searchable' => false],
        ];

        $viewData['sidebar'] = Menu::indicatorSidebar($idIndicator);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');
        $viewData['isDetailV2'] = true;

        $this->service->setIndicatorId($idIndicator);
        $this->service->setCellCategory(IndikatorCell::CELL);

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($idIndicator)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-cell", includes: true);
        return WebController::create($this->defineFormFields($idIndicator), IndikatorCell::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, int $idIndicator)
    {
        // add Request
        $request = $request->merge(['id_indikator_laporan_kinerja' => $idIndicator, 'kategori_cell' => IndikatorCell::CELL]);

        return WebController::store($this->service, $request, $this->defineFormFields($idIndicator), IndikatorCell::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(int $idIndicator, int $id)
    {
        $cards = $this->defineFormFields($idIndicator);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-cell", includes: true);
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, IndikatorCell::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(int $idIndicator, int $id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, "indicatorSidebar.{$idIndicator}.indikator-cell", includes: true);
        return WebController::edit($this->service, $id, $this->defineFormFields($idIndicator), IndikatorCell::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, int $idIndicator, int $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields($idIndicator), IndikatorCell::class);
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
    private function defineFormFields()
    {
        return [
            ['field' => 'column_to'],
            ['field' => 'row_to'],
            ['field' => 'jenis_cell', 'options' => IndikatorCell::CELL_TYPE],
            ['field' => 'posisi_label'],
            ['field' => 'nama'],
            ['field' => 'dapat_dilihat', 'control' => 'switch'],
            ['field' => 'id_indikator_laporan_kinerja', 'type' => 'hidden'],
            ['field' => 'kategori_cell', 'type' => 'hidden'],
        ];
    }
}
