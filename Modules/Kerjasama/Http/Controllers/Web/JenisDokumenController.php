<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\JenisDokumen;
use Modules\Kerjasama\Services\JenisDokumenManagementService;

class JenisDokumenController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JenisDokumenManagementService $service)
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
			['field' => 'jenis_dokumen'],
			['field' => 'action', 'component' => 'isian_default_reference'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('master');

        return WebController::index($this->service, $request, $header, [], $viewData, model: JenisDokumen::class, isReference: true);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JenisDokumen::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JenisDokumen::class, isReference: true);
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
			['field' => 'jenis_dokumen'],
			// ['field' => 'keterangan'],
        ];
    }
}
