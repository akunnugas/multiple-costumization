<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Services\JenjangPendidikanManagementService;
use Modules\SPMI\Helpers\Menu;

class JenjangPendidikanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JenjangPendidikanManagementService $service)
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
            ['field' => 'kode_jenjang'],
            ['field' => 'nama_jenjang'],
            ['field' => 'nama_jenjang_en'],
            ['field' => 'apakah_pt', 'component' => 'boolean', 'searchable' => false],
            ['field' => 'apakah_pasca', 'component' => 'boolean', 'searchable' => false],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('organization');
        $viewData['withSync'] = true;

        // saat ini hak akses delete tidak dapat digunakan, karena sync ke akademik
        $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'put' => false])]);

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), JenjangPendidikan::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JenjangPendidikan::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $viewData['isDetailV2'] = true;

        // saat ini hak akses edit tidak dapat digunakan, karena sync ke akademik
        $request->merge(['permission' => array_merge($request->permission, ['put' => false])]);

        return WebController::show($this->service, $id, $cards, JenjangPendidikan::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), JenjangPendidikan::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JenjangPendidikan::class);
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
            ['field' => 'kode_jenjang'],
            ['field' => 'nama_jenjang'],
            ['field' => 'nama_jenjang_en'],
            ['field' => 'urutan'],
            ['field' => 'apakah_akademik', 'control' => 'radio', 'options' => [1 => 'Ya', 0 => 'Tidak']],
            ['field' => 'apakah_pt', 'control' => 'radio', 'options' => [1 => 'Ya', 0 => 'Tidak']],
            ['field' => 'apakah_pasca', 'control' => 'radio', 'options' => [1 => 'Ya', 0 => 'Tidak']],
        ];
    }

    /**
     * Sync From Siakad v1
     *
     * @return Renderable
     */
    public function sync()
    {
        return WebController::sync($this->service);
    }
}
