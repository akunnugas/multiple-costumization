<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Services\AuditPeriodeManagementService;

class AuditPeriodeController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AuditPeriodeManagementService $service)
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
            [
                'field' => 'tahun_audit',
                'order' => 'desc', 
                'pattern' => '\d*', 
                'type' => 'number',
                'onKeyPress' => 'if(this.value.length == 4) return false;', 
                'step' => '0',
                'oninput' => "this.value = this.value.replace(/[+\-e.]/gi, '')"
            ],
            ['field' => 'tanggal_mulai', 'type' => 'date', 'component' => 'tanggal_default', 'searchable' => false],
            ['field' => 'tanggal_selesai', 'type' => 'date', 'component' => 'tanggal_default', 'searchable' => false],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');
        $viewData['withSync'] = true;

        return WebController::index($this->service, $request, $header, [], $viewData, isReference: true, model: AuditPeriode::class);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), AuditPeriode::class, isReference: true);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), AuditPeriode::class, isReference: true);
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
     * Sync From Siakad v1
     *
     * @return Renderable
     */
    public function sync()
    {
        return WebController::sync($this->service);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'tahun_audit'],
            ['field' => 'tanggal_mulai', 'type' => 'date'],
            ['field' => 'tanggal_selesai', 'type' => 'date'],
        ];
    }
}
