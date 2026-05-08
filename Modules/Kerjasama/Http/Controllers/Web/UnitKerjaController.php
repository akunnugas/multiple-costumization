<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\JenisDokumen;
use Modules\Kerjasama\Services\JenisDokumenManagementService;

class UnitKerjaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private UnitKerjaManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {

        $header = [
            ['field' => 'kode_unit'],
            ['field' => 'nama_unit'],
            ['field' => 'parent_unit'],
            ['field' => 'apakah_aktif', 'component' => 'apakah_aktif_unit_kerja', 'searchable' => false],
            ['field' => 'jenis_unit', 'component' => 'jenis_unit_kerja', 'searchable' => false],
        ];
        $viewData = [
            'withSync'   => true,
            'canCreate'  => false,
            'canDelete'  => false,
            'showDetail' => false,
            'syncLabel'  => 'Sinkronasi Unit Kerja',
            'sidebar'    => Menu::masterSidebar('master'),
        ];
        return WebController::index($this->service, $request, $header, [], $viewData, model: UnitKerja::class);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), UnitKerja::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), UnitKerja::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, UnitKerja::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), UnitKerja::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), UnitKerja::class);
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
    public function sync()
    {
        return WebController::sync($this->service);
    }
    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'nama_unit'],
            ['field' => 'kode_unit'],
            ['field' => 'parent_unit', 'type' => 'select', 'options' => $this->service->getParentUnitOptions()],
            ['field' => 'jenis_unit', 'type' => 'select', 'options' => $this->service->getJenisUnitOptions()],
            ['field' => 'apakah_aktif', 'type' => 'checkbox'],
            // ['field' => 'keterangan'],
        ];
    }
}
