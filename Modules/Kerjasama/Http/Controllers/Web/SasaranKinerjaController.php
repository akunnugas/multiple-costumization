<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\SasaranKinerja;
use Modules\Kerjasama\Services\SasaranKinerjaManagementService;

class SasaranKinerjaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SasaranKinerjaManagementService $service)
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
            ['field' => 'sasaran'],
            ['field' => 'keterangan'],
            ['field' => 'level', 'component' => true],
            ['field' => 'count_indikator', 'searchable' => false, 'sortable' => false, 'component' => true],
            ['field' => 'action', 'component' => 'isian_default']
        ];

        $viewData['sidebar'] = Menu::masterSidebar('master');

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $data = $this->service->show($id);

        if (Error::isError($data)) {
            return $data->redirectBack();
        }

        $cards = $this->defineFormFields();
        $cards['informasi-sasaran-kinerja']['title'] = $data['sasaran'];
        $cards['informasi-sasaran-kinerja']['subtitle'] = "Detail informasi terkait data sasaran kinerja dan indikator sasaran";

        if ($data['isian_default']) {
            $cards['informasi-sasaran-kinerja']['edit_url'] = null;
        }

        return WebController::show(
            $this->service, 
            $id, 
            $cards, 
            SasaranKinerja::class,
            data: $data
        );
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
            'informasi-sasaran-kinerja' => [
                'title' => 'Sasaran Kinerja',
                'subtitle' => 'Informasi Sasaran Kinerja',
                'icon' => 'bookmark-check',
                'edit_url' => url()->current() . '/edit',
                'items' => [
                    ['field' => 'sasaran'],
                    ['field' => 'keterangan'],
                    ['field' => 'level'],
                ]
            ],
            '_' => [
                'title' => '_',
                'items' => [
                    ['field' => '_']
                ]
            ]
        ];
    }
}
