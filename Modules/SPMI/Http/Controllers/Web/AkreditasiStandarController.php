<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Services\AkreditasiStandarManagementService;
use Modules\SPMI\Services\JenisStandarManagementService;

class AkreditasiStandarController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AkreditasiStandarManagementService $service)
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
            ['field' => 'kode_standar'],
            ['field' => 'nama_standar'],
            ['field' => 'apakah_data_default', 'label' => 'Status', 'component' => "apakah_data_default", 'searchable' => false],
            ['field' => 'action', 'component' => 'standar_akreditasi_action', 'searchable' => false]
        ];

        $optJenisStandar = JenisStandar::options();

        $idJenisStandar = $request->filter['id_jenis_standar'] ?? session('filter_jenis_standar') ?? array_key_first($optJenisStandar);

        if (!empty($idJenisStandar)) {
            session(['filter_jenis_standar' => $idJenisStandar]);
        }
        if (
            !isset($optJenisStandar[$idJenisStandar])
        ) {
            $idJenisStandar = array_key_first($optJenisStandar);
            session(['filter_jenis_standar' => $idJenisStandar]);
        }

        $filter = [
            'id_jenis_standar' => [
                'options' => $optJenisStandar,
                'label' => 'Jenis Standar',
                'selected' => $idJenisStandar
            ],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-standar');
        return WebController::create($this->defineFormFields(), AkreditasiStandar::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), AkreditasiStandar::class);
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

        $akreditasiStandar = AkreditasiStandar::find($id);
        if ($akreditasiStandar->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-standar');
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, AkreditasiStandar::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-standar');
        return WebController::edit($this->service, $id, $this->defineFormFields(), AkreditasiStandar::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), AkreditasiStandar::class);
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
        $optionStandard = new JenisStandarManagementService();

        return [
            ['field' => 'id_jenis_standar', 'options' => $optionStandard->getListOption(), 'control' => 'select', 'selected' => session('filter_jenis_standar')],
            ['field' => 'kode_standar'],
            ['field' => 'nama_standar']
        ];
    }
}
