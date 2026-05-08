<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Error;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SpmiPeringkat;
use Modules\SPMI\Services\SpmiPeringkatManagementService;

class SpmiPeringkatController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SpmiPeringkatManagementService $service)
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
            ['field' => 'kode_spmi_peringkat', 'maxlength' => 10],
            ['field' => 'nama_spmi_peringkat'],
            ['field' => 'skor_minimal', 'type' => 'number', 'min' => 0, 'step' => '0.01', 'searchable' => false,],
            ['field' => 'skor_maksimal', 'type' => 'number', 'min' => 0, 'step' => '0.01', 'searchable' => false],
            ['field' => 'deskripsi'],
        ];

        if (empty($request->sort)) {
            $request->merge(['sort' => '3', 'sortAsc' => true]);
        }

        $optAuditPeriode = AuditPeriode::options();
        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_peringkat_ami_periode') ?? array_key_first($optAuditPeriode);

        if (!empty($idAuditPeriode)) {
            session(['filter_peringkat_ami_periode' => $idAuditPeriode]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode,
            ],
        ];

        $selectedAuditPeriode = $filter['id_audit_periode']['selected'];

        // Tambahan form hidden untuk id_audit_periode
        $header[] = ['field' => 'id_audit_periode', 'type' => 'hidden', 'value' => $selectedAuditPeriode];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');
        $viewData['title'] = 'Peringkat AMI';
        $viewData['subtitle'] = '';

        return WebController::index($this->service, $request, $header, $filter, $viewData, isReference: true, model: SpmiPeringkat::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $fields = $this->defineFormFields();
        array_unshift($fields, ['field' => 'id_audit_periode']);

        return WebController::store($this->service, $request, $fields, SpmiPeringkat::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $fields = $this->defineFormFields();
        array_unshift($fields, ['field' => 'id_audit_periode']);

        return WebController::update($this->service, $id, $request, $this->defineFormFields(), SpmiPeringkat::class, isReference: true);
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
            ['field' => 'kode_spmi_peringkat'],
            ['field' => 'nama_spmi_peringkat'],
            ['field' => 'skor_minimal'],
            ['field' => 'skor_maksimal'],
            ['field' => 'deskripsi'],
        ];
    }
}
