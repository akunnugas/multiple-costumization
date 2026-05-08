<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Modules\Litabmas\Services\JenisOutputPenelitianService;

class JenisOutputPenelitianController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JenisOutputPenelitianService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $permissions = request()->permission;

        $header = [
            ['field' => 'nama_output', 'label' => 'Nama Luaran'],
        ];

        $viewData = [
            'title' => 'Luaran',
            'showDeleteChecked' => false,
            'showNumber' => true,
            'navTab' => Menu::navTabs('final-result-activity'),
            'staticAlert' => [
                'message' => 'Masukkan hasil akhir kegiatan berupa luaran serta outcome sebagai referensi yang akan digunakan pada penentuan klaster.',
            ],
            'canUpdate' => $permissions['put'],
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            viewData: $viewData,
            model: JenisOutputPenelitian::class,
            isReference: true
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JenisOutputPenelitian::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JenisOutputPenelitian::class, isReference: true);
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
            ['field' => 'nama_output'],
        ];
    }
}
