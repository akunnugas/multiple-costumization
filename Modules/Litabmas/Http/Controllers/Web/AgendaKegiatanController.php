<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Services\AgendaKegiatanService;

class AgendaKegiatanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AgendaKegiatanService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        // overide permissions to false, except false
        $permissions = request()->permission;
        $permissions['post'] = false;
        $permissions['put'] = false;
        $permissions['delete'] = false;
        $request->merge(['permission' => $permissions]);

        $viewData = [
            'title' => 'Tahapan Kegiatan',
            'showNumber' => true,
            'staticAlert' => [
                'message' => 'Daftar Tahapan Kegiatan ini akan digunakan untuk penentuan tahapan pada sumber pendanaan'
            ]
        ];

        if (empty($request->perPage)) {
            $request->merge(['perPage' => 20]);
        }

        return WebController::index(
            $this->service,
            $request,
            $this->defineFormFields(),
            viewData: $viewData,
            model: AgendaKegiatan::class,
            isReference: true
        );
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'nama_agenda'],
        ];
    }
}
