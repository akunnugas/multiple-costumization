<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use Modules\SPMI\Services\SkorMatriksPredikatManagementService;

class SkorMatriksPredikatController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SkorMatriksPredikatManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param int $penilaianPanduanId
     * @return Renderable
     */
    public function index(Request $request, $penilaianPanduanId)
    {
        $header = $this->defineFormFields();

        $viewData['isDetailV2'] = true;
        $this->service->setParentResourceId($penilaianPanduanId);

        $penilaianPanduan = PenilaianPanduan::findOrFail($penilaianPanduanId);

        $viewData['sidebar'] = Menu::penilaianPanduanSidebar($penilaianPanduanId);
        if ($penilaianPanduan->apakah_data_default) {
            $request->merge([
                'permission' => [
                    'post' => false,
                    'put' => false,
                    'delete' => false,
                    'custom' => false,
                    'get' => true,
                ]
            ]);
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

        $viewData['isDisableSearch'] = true;

        return WebController::index($this->service, $request, $header, [], $viewData, isReference: true);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param int $idPenilaianPanduan
     * @return Renderable
     */
    public function store(Request $request, $idPenilaianPanduan)
    {
        $request->merge(['id_penilaian_panduan' => $idPenilaianPanduan]);
        $fields = $this->defineFormFields();

        // add registration period id to fields
        array_unshift($fields, ['field' => 'id_penilaian_panduan']);

        return WebController::store($this->service, $request, $fields, SkorMatriksPredikatPenilaian::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @param int $PenilaianMatriksId
     * @return Renderable
     */
    public function update(Request $request, $id, $idSkorMatriksPredikat)
    {
        $request->merge(['id_penilaian_panduan' => $id]);
        $fields = $this->defineFormFields();

        // add registration period id to fields
        array_unshift($fields, ['field' => 'id_penilaian_panduan']);

        return WebController::update($this->service, $idSkorMatriksPredikat, $request, $fields, SkorMatriksPredikatPenilaian::class, isReference: true);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $idSkorMatriksPredikat)
    {
        return WebController::destroy($this->service, $idSkorMatriksPredikat);
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
            ['field' => 'nilai', 'type' => 'select', 'options' => [0, 1, 2, 3, 4], 'required' => true],
            ['field' => 'deskripsi', 'control' => 'textarea', 'maxlength' => 100, 'helper' => 'Maksimal 100 karakter.'],
        ];
    }
}
