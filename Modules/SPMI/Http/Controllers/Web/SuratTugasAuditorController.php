<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Services\SuratTugasAuditorManagementService;

class SuratTugasAuditorController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SuratTugasAuditorManagementService $service)
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
            ['field' => 'periode_audit', 'label' => 'Periode AMI', 'order' => 'desc'],
            ['field' => 'nomor_surat_tugas', 'label' => 'Nomor ST Auditor'],
            ['field' => 'tanggal_surat_tugas', 'label' => 'Tanggal ST Diterbitkan', 'component' => true, 'searchable' => false],
            ['field' => 'tanggal_surat_tugas_range', 'label' => 'Masa Berlaku ST', 'component' => true, 'searchable' => false],
            ['field' => 'nama_dokumen_surat_tugas', 'label' => 'Dokumen ST Auditor', 'component' => true],
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
                'title' => 'Tambahkan Surat Tugas AMI',
                'subtitle' => 'Silakan Upload Surat Tugas AMI sebagai dokumen kegiatan, serta untuk mendatakan Auditee dan Auditor yang bertugas'
            ],
            'title' => 'Surat Tugas AMI',
            'subtitle' => '',
            'createLabel' => 'Tambah Surat Tugas AMI'
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), SuratTugasAuditor::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), SuratTugasAuditor::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, SuratTugasAuditor::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), SuratTugasAuditor::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), SuratTugasAuditor::class);
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
            ['field' => 'nomor_surat_tugas'],
            ['field' => 'tanggal_surat_tugas'],
            ['field' => 'tanggal_mulai'],
            ['field' => 'tanggal_selesai'],
            ['field' => 'auditor', 'label' => 'Daftar Pegawai', 'grid' => false],
            ['field' => 'id_dokumen']
        ];
    }
}
