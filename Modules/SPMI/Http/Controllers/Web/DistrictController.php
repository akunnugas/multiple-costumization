<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Wilayah;
use Modules\Core\Services\WilayahManagementService;
use Modules\SPMI\Helpers\Menu;

class DistrictController extends Controller
{
    protected $level = Wilayah::LEVEL_DISTRICT;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private WilayahManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'region_id'],
            ['field' => 'name'],
            ['field' => 'dikti_code'],
        ];

        $filter = [
            'id_parent' => ['options' => $this->service->options(Wilayah::LEVEL_CITY), 'label' => 'Kabupaten / Kota'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('region');

        return WebController::index($this->service, $request, $header, $filter, $viewData, customMethod: 'indexDistricts');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), Wilayah::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->merge([
            // Menambahkan level negara
            'region_level' => $this->level
        ]);

        $field = array_merge($this->defineFormFields(), [
            ['field' => 'region_level'],
        ]);

        return WebController::store($this->service, $request, $field, Wilayah::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, Wilayah::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), Wilayah::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), Wilayah::class);
    }

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

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'region_id'],
            ['field' => 'name'],
            ['field' => 'dikti_code', 'type' => 'number'],
            ['field' => 'id_parent', 'options' => $this->service->options(Wilayah::LEVEL_CITY), 'control' => 'select'],
        ];
    }
}
