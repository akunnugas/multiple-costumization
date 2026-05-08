<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SkAuditor;
use Modules\SPMI\Services\SkAuditorManagementService;

class SkAuditorController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SkAuditorManagementService $service)
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
            ['field' => 'periode_audit', 'order' => 'desc'],
            ['field' => 'nomor_sk', 'label' => 'Nomor SK Auditor'],
            ['field' => 'tanggal_diterbitkan', 'label' => 'Tanggal SK Diterbitkan', 'component' => true],
            ['field' => 'tanggal_diterbitkan_range', 'label' => 'Masa Berlaku SK', 'component' => true],
            ['field' => 'nama_dokumen_sk', 'label' => 'Dokumen SK Auditor', 'component' => true],
        ];

        $filter = [
            'id_audit_periode' => [
                'options' => ['-' => 'Semua Periode'] + AuditPeriode::options(),
                'label' => 'Periode AMI',
                'hideLabel' => true,
                // 'empty_selected' => AuditPeriode::findNowYearPeriod()?->id
            ],
        ];

        $viewData = [
            'emptyState' => [
                'title' => 'Tambah SK atau Auditor',
                'subtitle' => 'Silakan upload surat keputusan sebagai bukti pelaksanaan AMI dan datakan nama auditor yang bertugas'
            ],
            'title' => 'Surat Keputusan Auditor',
            'subtitle' => '',
            'createLabel' => 'Tambah SK Auditor'
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), SkAuditor::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), SkAuditor::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, SkAuditor::class, ['isDetailV2' => true]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), SkAuditor::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), SkAuditor::class);
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
            ['field' => 'id_audit_periode'],
            ['field' => 'nomor_sk'],
            ['field' => 'tanggal_diterbitkan'],
            ['field' => 'tanggal_awal_berlaku'],
            ['field' => 'tanggal_akhir_berlaku'],
            ['field' => 'data_pegawai', 'label' => 'Daftar Pegawai', 'grid' => false],
            ['field' => 'id_dokumen'],
        ];
    }
}
