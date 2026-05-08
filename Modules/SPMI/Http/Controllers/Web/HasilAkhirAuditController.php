<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\HasilAkhirAuditManagementService;

class HasilAkhirAuditController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private HasilAkhirAuditManagementService $service)
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
            ['field' => 'periode_audit', 'label' => 'Periode AMI'],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'nama_unit', 'Program Studi'],
            ['field' => 'nama_penilaian_panduan', 'Panduan Penilaian'],
            ['field' => 'nilai_iku', 'searchable' => false],
            ['field' => 'nilai_ikt', 'searchable' => false],
            ['field' => 'persentase_nilai_akhir', 'searchable' => false],
            ['field' => 'nama_spmi_peringkat'],
            ['field' => 'nama_peringkat_akreditasi'],
            ['field' => 'action', 'component' => 'hasil_akhir_audit', 'searchable' => false]
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = SuratTugasAuditorPegawai::optionProdiBySuratTugas(auth()->user()->id);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_hasil_akhir_audit_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_hasil_akhir_audit_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_hasil_akhir_audit_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_hasil_akhir_audit_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_hasil_akhir_audit_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_hasil_akhir_audit_unit_kerja' => $idUnitKerja]);
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
            'showDeleteChecked' => false,
            'canCreate' => false,
            'showNumber' => true,
            'emptyState' => [
                'title' => 'Belum Ada Data Hasil Akhir AMI',
                'subtitle' => 'Pastikan seluruh proses penilaian auditor, temuan, dan berita acara telah diselesaikan agar data hasil akhir dapat muncul.'
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
        return WebController::create($this->defineFormFields(), HasilAkhirAudit::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), HasilAkhirAudit::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();
        $cards[] = ['field' => 'butir_tidak_terpenuhi'];

        // Ambil data element kriteria
        $criteriaRawData = $this->service->showRawScoreChart($id);

        $hasilAkhirAudit = HasilAkhirAudit::findOrFail($id);
        $penilaianPanduan = $hasilAkhirAudit->penilaianAudit->penilaianPanduan;
        if (!$penilaianPanduan->apakah_menggunakan_peringkat) {
            foreach ($cards as $index => $card) {
                if ($card['field'] == 'id_akreditasi_peringkat') {
                    unset($cards[$index]);
                }
            }
        } else {
            foreach ($cards as $index => $card) {
                if ($card['field'] == 'nama_akreditasi_status') {
                    unset($cards[$index]);
                }
            }
        }

        return WebController::show($this->service, $id, $cards, HasilAkhirAudit::class)
            ->withCriteriaChart($criteriaRawData["chart"])
            ->withElementScoreData($criteriaRawData["data_table"]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), HasilAkhirAudit::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), HasilAkhirAudit::class);
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

    public function report($id)
    {
        $data = $this->service->generateReport($id);

        return view('spmi::laporan.hasil-akhir-audit')
            ->withData($data);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'id_audit_periode'],
            ['field' => 'nama_jadwal_audit'],
            ['field' => 'nilai_iku'],
            ['field' => 'id_unit'],
            ['field' => 'id_akreditasi_peringkat'],
            ['field' => 'panduan_penilaian'],
            ['field' => 'ketua_auditor'],
            ['field' => 'persentase_nilai_akhir'],
            ['field' => 'anggota_auditor'],
            ['field' => 'nama_spmi_peringkat'],
            ['field' => 'nama_akreditasi_status'],
            ['field' => 'apakah_syarat_terakreditasi_terpenuhi'],
            ['field' => 'kode_jenjang'],
        ];
    }
}
