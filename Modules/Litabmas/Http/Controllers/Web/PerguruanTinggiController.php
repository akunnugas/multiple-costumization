<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Services\PerguruanTinggiManagementService;

class PerguruanTinggiController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PerguruanTinggiManagementService $service)
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
            ['field' => 'kode_pt'],
            ['field' => 'nama_pt'],
            ['field' => 'alamat_pt'],
            ['field' => 'telepon_pt'],
        ];

        $viewData['withSync'] = true;

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['title'] = 'Tambah Data Perguruan Tinggi';

        return WebController::create($this->defineFormFields(), PerguruanTinggi::class, viewData: $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $model = PerguruanTinggi::class;

        return WebController::store($this->service, $request, $this->defineFormFields(), $model);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = [
            ['field' => 'kode_pt', 'label' => 'Kode'],
            ['field' => 'nama_pt', 'label' => 'Nama Perguruan Tinggi'],
            ['field' => 'alamat_pt', 'label' => 'Alamat'],
            ['field' => 'telepon_pt', 'label' => 'Telepon'],
        ];

        return WebController::show($this->service, $id, $cards, PerguruanTinggi::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), PerguruanTinggi::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), PerguruanTinggi::class);
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
            ['field' => 'kode_pt', 'label' => 'Kode', 'required' => true],
            ['field' => 'nama_pt', 'label' => 'Nama Perguruan Tinggi', 'required' => true],
            ['field' => 'alamat_pt', 'label' => 'Alamat', 'type' => 'textarea', 'required' => false],
            ['field' => 'telepon_pt', 'label' => 'Telepon', 'required' => false],
        ];
    }

    /**
     * Sync From Siakad v1
     *
     * @return Renderable
     */
    public function sync()
    {
        return WebController::sync($this->service, 'syncUniversitasFromSiakadV1');
    }
}
