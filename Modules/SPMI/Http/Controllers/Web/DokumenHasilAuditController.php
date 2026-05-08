<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\DokumenHasilAudit;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\DokumenHasilAuditManagementService;

class DokumenHasilAuditController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private DokumenHasilAuditManagementService $service)
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
            ['field' => 'nama_unit'],
            ['field' => 'nama_penilaian_panduan'],
            ['field' => 'nama_dokumen', 'sortable' => true, 'component' => true],
            ['field' => 'action', 'component' => 'dokumen_hasil_audit', 'searchable' => false]
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = SuratTugasAuditorPegawai::optionProdiBySuratTugas(auth()->user()->id);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_dokumen_hasil_audit_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_dokumen_hasil_audit_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_dokumen_hasil_audit_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_dokumen_hasil_audit_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_dokumen_hasil_audit_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_dokumen_hasil_audit_unit_kerja' => $idUnitKerja]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode
            ],
            'o.id_jenjang_pendidikan' => [
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
                'title' => 'Belum Ada Data Berita Acara',
                'subtitle' => 'Pastikan seluruh proses <a class="a-link" href="'.route('spmi.penilaian-auditor.index').'">Penilaian Auditor AMI</a> telah selesai dan difinalisasi agar data berita acara dapat ditampilkan.'
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
        return WebController::create($this->defineFormFields(), DokumenHasilAudit::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $successURL = route('spmi.dokumen-hasil-audit.index') . '?filter[id_audit_periode]=' . $request->id_audit_periode;

        try {
            return WebController::store($this->service, $request, $this->defineFormFields(), DokumenHasilAudit::class, isReference: true, successURL: $successURL, successMessage: 'Berhasil mengupload berita acara.');
        } catch (\Exception $e) {
            $error = new Error(exception: $e);
            return $error->redirectBack();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, DokumenHasilAudit::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), DokumenHasilAudit::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {
            return WebController::update($this->service, $id, $request, $this->defineFormFields(), DokumenHasilAudit::class, isReference: true, successMessage: 'Berhasil mengupload berita acara.');
        } catch (\Exception $e) {
            $error = new Error(exception: $e);
            return $error->redirectBack();
        }
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
            ['field' => 'id_unit'],
            ['field' => 'id_dokumen'],
            ['field' => 'id_audit_periode'],
            ['field' => 'id_jadwal_audit'],
        ];
    }
}
