<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\JenisStandar;
use Modules\SPMI\Services\JenisStandarManagementService;

class JenisStandarController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JenisStandarManagementService $service)
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
            ['field' => 'kode_jenis_standar'],
            ['field' => 'nama_jenis_standar', 'label' => 'Jenis Standar'],
            ['field' => 'total_standart', 'label' => 'Jumlah Standar', 'searchable' => false],
            ['field' => 'apakah_data_default', 'label' => 'Sumber Data', 'component' => "apakah_data_default", 'searchable' => false],
            ['field' => 'action', 'component' => 'jenis_standar_action', 'searchable' => false]
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
        return WebController::create($this->defineFormFields(), JenisStandar::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JenisStandar::class);
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

        $jenisStandar = JenisStandar::find($id);
        if ($jenisStandar->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        return WebController::show($this->service, $id, $cards, JenisStandar::class, ['isDetailV2' => true]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), JenisStandar::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JenisStandar::class);
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
            ['field' => 'kode_jenis_standar'],
			['field' => 'nama_jenis_standar'],
        ];
    }
}
