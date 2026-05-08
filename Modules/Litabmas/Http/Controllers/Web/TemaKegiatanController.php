<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\TemaKegiatan;
use Modules\Litabmas\Services\TemaKegiatanService;

class TemaKegiatanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private TemaKegiatanService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = $this->defineFormFields();

        $viewData = [
            'title' => 'Tema Kegiatan',
            'showDeleteChecked' => false,
            'showNumber' => true,
            'staticAlert' => [
                'message' => 'Masukkan tema atau isu sentral yang menjadi fokus utama kegiatan penelitian atau pengabdian masyarakat.'
            ]
        ];

        if (empty($request->sort)) {
            $request->merge(['sort' => '2', 'sortAsc' => true]);
        }

        return WebController::index(
            $this->service,
            $request,
            $header,
            viewData: $viewData,
            model: TemaKegiatan::class,
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
        return WebController::store($this->service, $request, $this->defineFormFields(), TemaKegiatan::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), TemaKegiatan::class, isReference: true);
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
            ['field' => 'nama_tema'],

            // hidden field
            ['field' => 'waktu_dibuat', 'type' => 'hidden', 'searchable' => false],
        ];
    }
}
