<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\PenilaianAuditorManagementService;

class PenilaianAuditorController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PenilaianAuditorManagementService $service)
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
            ['field' => 'periode_audit'],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'nama_unit', 'order' => 'asc'],
            ['field' => 'nama_penilaian_panduan'],
            ['field' => 'status_penilaian', 'component' => true, 'searchable' => false],
            ['field' => 'apakah_punya_feedback', 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => 'penilaian', 'searchable' => false]
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = SuratTugasAuditorPegawai::optionProdiBySuratTugas(auth()->user()->id);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_penilaian_auditor_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_penilaian_auditor_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_penilaian_auditor_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_penilaian_auditor_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_penilaian_auditor_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_penilaian_auditor_unit_kerja' => $idUnitKerja]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode
            ],
            'id_jenjang_pendidikan' => [
                'options' => $optJenjangPendidikan,
                'label' => 'Jenjang Pendidikan',
                'selected' => $idJenjangPendidikan,
                'is_empty' => true
            ],
            'id_unit_kerja' => [
                'options' => $optUnitKerja,
                'label' => 'Unit Kerja',
                'selected' => $idUnitKerja,
                'is_empty' => true
            ],
        ];

        $viewData = [
            'showNumber' => true,
            'emptyState' => [
                'title' => 'Belum Ada Data Penilaian',
                'subtitle' => 'Belum ada data penilaian karena auditor belum dipetakan. Silakan tambahkan atau atur penugasan auditor di halaman <a class="a-link" href="' . route('spmi.surat-tugas-auditor.index') . '">Surat Tugas AMI</a>.'
            ]
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), PenilaianAudit::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), PenilaianAudit::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, PenilaianAudit::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), PenilaianAudit::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), PenilaianAudit::class);
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
        return [];
    }
}
