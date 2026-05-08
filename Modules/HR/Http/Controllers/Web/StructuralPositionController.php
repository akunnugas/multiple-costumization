<?php

namespace Modules\HR\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\HR\Helpers\Menu;
use Modules\HR\Models\StructuralPosition;
use Modules\HR\Services\StructuralPositionManagementService;

class StructuralPositionController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private StructuralPositionManagementService $service)
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
            ['field' => 'code'],
            ['field' => 'name'],
            ['field' => 'parent'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('employee');

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), StructuralPosition::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), StructuralPosition::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return WebController::show($this->service, $id, $this->defineFormFields(), StructuralPosition::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), StructuralPosition::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), StructuralPosition::class);
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
     * 
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'code'],
            ['field' => 'structural_position_type_id',],
            ['field' => 'name'],
            ['field' => 'email'],
            ['field' => 'abbreviation'],
            ['field' => 'parent_id'],
            ['field' => 'organization_id'],
            ['field' => 'echelon_id'],
            ['field' => 'min_position_level_id'],
            ['field' => 'max_position_level_id'],
            ['field' => 'description', 'control' => 'textarea'],
            ['field' => 'is_active', 'control' => 'radio', 'options' => [1 => 'Aktif', 0 => 'Tidak Aktif']],
            ['field' => 'is_leader', 'control' => 'radio', 'options' => [1 => 'Ya', 0 => 'Tidak']],
        ];
    }
}
