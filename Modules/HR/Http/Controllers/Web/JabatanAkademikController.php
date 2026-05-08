<?php

namespace Modules\HR\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\HR\Helpers\Menu;
use Modules\HR\Models\JabatanAkademik;
use Modules\HR\Services\JabatanAkademikManagementService;

class JabatanAkademikController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JabatanAkademikManagementService $service)
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
            ['field' => 'kode_jabatan_akademik'],
            ['field' => 'nama_jabatan_akademik'],
            ['field' => 'jenis_jabatan_akademik'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('employee');

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), JabatanAkademik::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JabatanAkademik::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return WebController::show($this->service, $id, $this->defineFormFields(), JabatanAkademik::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), JabatanAkademik::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JabatanAkademik::class);
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
            ['field' => 'kode_jabatan_akademik'],
            ['field' => 'nama_jabatan_akademik'],
            ['field' => 'jenis_jabatan_akademik'],
        ];
    }
}
