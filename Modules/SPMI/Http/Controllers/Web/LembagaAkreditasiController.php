<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Services\LembagaAkreditasiManagementService;

class LembagaAkreditasiController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private LembagaAkreditasiManagementService $service)
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
            ['field' => 'kode_lembaga'],
			['field' => 'nama_lembaga'],
			['field' => 'nama_singkat_lembaga'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]); // harcoded for now

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.lembaga-akreditasi');
        return WebController::create($this->defineFormFields(), LembagaAkreditasi::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), LembagaAkreditasi::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.lembaga-akreditasi');
        $viewData['isDetailV2'] = true;

        $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]); // harcoded for now

        return WebController::show($this->service, $id, $cards, LembagaAkreditasi::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.lembaga-akreditasi');
        return WebController::edit($this->service, $id, $this->defineFormFields(), LembagaAkreditasi::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), LembagaAkreditasi::class);
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
            ['field' => 'kode_lembaga'],
			['field' => 'nama_lembaga'],
			['field' => 'nama_singkat_lembaga'],
        ];
    }
}
