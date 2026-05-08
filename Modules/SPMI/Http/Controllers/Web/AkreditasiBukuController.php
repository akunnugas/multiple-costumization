<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Services\AkreditasiBukuManagementService;

class AkreditasiBukuController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AkreditasiBukuManagementService $service)
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
            ['field' => 'kode_buku'],
			['field' => 'nama_buku'],
			['field' => 'jenis_buku', 'options' => (new AkreditasiBukuManagementService())->getListTypeBook()],
            ['field' => 'apakah_data_default', 'label' => 'Sumber Buku', 'component' => "apakah_data_default", 'searchable' => false],
            ['field' => 'action', 'component' => 'akreditasi_buku_action', 'searchable' => false]
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-buku');
        return WebController::create($this->defineFormFields(), AkreditasiBuku::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), AkreditasiBuku::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $cards[] = ['field' => 'apakah_data_default', 'control' => 'checkbox', 'disabled' => true];

        $akreditasiBuku = AkreditasiBuku::find($id);
        if ($akreditasiBuku->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-buku');
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, AkreditasiBuku::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-buku');
        return WebController::edit($this->service, $id, $this->defineFormFields(), AkreditasiBuku::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), AkreditasiBuku::class);
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
        $optionsTypeBook = new AkreditasiBukuManagementService();
        return [
			['field' => 'jenis_buku', 'options' => $optionsTypeBook->getListTypeBook(), 'control' => 'select'],
            ['field' => 'kode_buku'],
			['field' => 'nama_buku'],
        ];
    }
}
