<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\AuditTemuan;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\AuditTemuanManagementService;

class AuditTemuanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AuditTemuanManagementService $service)
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
            ['field' => 'periode_audit', 'searchable' => false],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'penilaian_panduan', 'label' => 'Panduan Penilaian'],
            ['field' => 'nama_unit', 'order' => 'asc'],
            ['field' => 'ketua_auditor'],
            ['field' => 'status_temuan', 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => 'audit_temuan', 'searchable' => false]
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = SuratTugasAuditorPegawai::optionProdiBySuratTugas(auth()->user()->id);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_temuan_auditor_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_temuan_auditor_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_temuan_auditor_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_temuan_auditor_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_temuan_auditor_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_temuan_auditor_unit_kerja' => $idUnitKerja]);
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
                'title' => 'Belum Ada Data Temuan Auditor',
                'subtitle' => 'Pastikan proses <b><a class="a-link" href="'.route('spmi.penilaian-auditor.index').'">Penilaian Auditor AMI</a></b> telah selesai 100% dan difinalisasi sebelum meninjau temuan.',
            ]
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the specified resource. Not Used.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, AuditTemuan::class);
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
