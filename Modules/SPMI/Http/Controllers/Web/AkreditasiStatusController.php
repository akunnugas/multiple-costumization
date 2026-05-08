<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiStatus;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\AkreditasiStatusManagementService;

class AkreditasiStatusController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AkreditasiStatusManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request, $PenilaianPanduanId)
    {
        $header = [
            ['field' => 'kode_status'],
            ['field' => 'nama_status'],
            ['field' => 'nilai_minimal', 'type' => 'number', 'min' => 0, 'max' => 500, 'step' => '0.01', 'searchable' => false,],
            ['field' => 'nilai_maksimal', 'type' => 'number', 'min' => 0, 'max' => 500, 'step' => '0.01', 'searchable' => false,],
        ];

        if (empty($request->sort)) {
            $request->merge(['sort' => '3', 'sortAsc' => true]);
        }

        $viewData['sidebar'] = Menu::penilaianPanduanSidebar($PenilaianPanduanId);
        $viewData['isDetailV2'] = true;
        $this->service->setParentResourceId($PenilaianPanduanId);

        $penilaianPanduan = PenilaianPanduan::findOrFail($PenilaianPanduanId);
        if ($penilaianPanduan->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        if ($penilaianPanduan->apakah_menggunakan_peringkat) {
            if (isset($viewData['sidebar']['items'][1]['items'])) {
                $viewData['sidebar']['items'][1]['items'] = array_values(array_filter(
                    $viewData['sidebar']['items'][1]['items'],
                    function ($item) {
                        return !(isset($item['path']) && str_contains($item['path'], '/akreditasi-status'));
                    }
                ));
            }
        } else {
            if (isset($viewData['sidebar']['items'][1]['items'])) {
                $viewData['sidebar']['items'][1]['items'] = array_values(array_filter(
                    $viewData['sidebar']['items'][1]['items'],
                    function ($item) {
                        return !(isset($item['path']) && str_contains($item['path'], '/akreditasi-peringkat'));
                    }
                ));
            }
        }

        return WebController::index($this->service, $request, $header, [], $viewData,  model: AkreditasiStatus::class, isReference: true);
    }

    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $viewData['sidebar'] = Menu::penilaianPanduanSidebar($id);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-panduan');
        $viewData['isDetailV2'] = true;

        return WebController::show(
            service: $this->service,
            id: $id,
            cards: $cards,
            model: AkreditasiStatus::class,
            viewData: $viewData,
            data: [
                'cards' => $cards,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $PenilaianPanduanId)
    {
        $request->merge(['id_penilaian_panduan' => $PenilaianPanduanId]);
        $fields = $this->defineFormFields();

        // add registration period id to fields
        array_unshift($fields, ['field' => 'id_penilaian_panduan']);

        return WebController::store($this->service, $request, $fields, AkreditasiStatus::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id, $PenilaianPanduanId)
    {
        return WebController::update($this->service, $PenilaianPanduanId, $request, $this->defineFormFields(), AkreditasiStatus::class, isReference: true);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $PenilaianPanduanId)
    {
        return WebController::destroy($this->service, $PenilaianPanduanId);
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
            ['field' => 'kode_peringkat'],
            ['field' => 'nama_peringkat_akreditasi'],
            ['field' => 'nilai_minimal'],
            ['field' => 'nilai_maksimal'],
            ['field' => 'deskripsi'],
        ];
    }
}
