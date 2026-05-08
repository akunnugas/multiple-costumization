<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Services\JadwalAuditManagementService;

class JadwalAuditController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private JadwalAuditManagementService $service) {}

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
            ['field' => 'unit', 'label' => 'Unit Kerja / Program Studi', 'component' => true, 'sortable' => false, 'tdspan' => true],
            ['field' => 'tanggal_pengisian', 'label' => 'Tanggal Pengisian Laporan', 'component' => true],
            ['field' => 'tanggal_penilaian', 'label' => 'Tanggal Penilaian Auditor', 'component' => true],
            ['field' => 'apakah_audit_aktif', 'component' => true, 'sortable' => false],
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_jadwal_audit_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_jadwal_audit_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_jadwal_audit_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_jadwal_audit_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_jadwal_audit_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_jadwal_audit_unit_kerja' => $idUnitKerja]);
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
            'emptyState' => [
                'title' => 'Buat Jadwal Audit Mutu Internal',
                'subtitle' => 'Tentukan jadwal Audit Mutu Internal untuk setiap program studi yang Anda miliki'
            ],
            'title' => 'Jadwal Audit Mutu Internal',
            'subtitle' => '',
            'createLabel' => 'Buat Jadwal AMI'
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $fields = [
            ['field' => 'id_audit_periode'],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'filling_date', 'component' => true, 'module' => Modul::CODE_SPMI],
            ['field' => 'assessment_date', 'component' => true, 'module' => Modul::CODE_SPMI],
            ['field' => 'apakah_audit_aktif', 'type' => 'select', 'options' => [0 => 'Tidak Aktif', 1 => 'Aktif'], 'selected' => 1],
            [
                'field' => 'unit',
                'control' => 'checkbox',
                'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM])
            ],
        ];

        return WebController::create($fields, JadwalAudit::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JadwalAudit::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        $viewData['isDetailV2'] = true;
        $viewData['page_conf'][] = ['custom_page' => ['component' => 'custom-show', 'data' => [
            'units' => DB::table('spmi.jadwal_audit_unit as apu')
                ->join('core.unit_kerja as u', 'u.id', '=', 'apu.id_unit')
                ->join('spmi.pengisian_panduan as pp', 'pp.id', '=', 'apu.id_pengisian_panduan')
                ->join('spmi.penilaian_panduan as pnp', 'pnp.id', '=', 'apu.id_penilaian_panduan')
                ->join('core.jenjang_pendidikan as jp', 'jp.id', '=', 'u.id_jenjang_pendidikan')
                ->select('u.nama_unit', 'pp.nama_singkat as panduan_pengisian', 'pnp.nama_singkat as panduan_penilaian', 'jp.kode_jenjang as jenjang_pendidikan')
                ->where('apu.id_jadwal_audit', $id)
                ->where('u.' . UnitKerja::DELETED_AT, null)
                ->get()
                ->toArray(),
        ]]];

        return WebController::show($this->service, $id, $cards, JadwalAudit::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $fields = [
            ['field' => 'id_audit_periode'],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'tanggal_pengisian', 'component' => true, 'module' => Modul::CODE_SPMI],
            ['field' => 'tanggal_penilaian', 'component' => true, 'module' => Modul::CODE_SPMI],
            ['field' => 'apakah_audit_aktif', 'type' => 'select', 'options' => [0 => 'Tidak Aktif', 1 => 'Aktif'], 'selected' => 1],
            [
                'field' => 'unit',
                'control' => 'checkbox',
                'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM])
            ],
        ];

        return WebController::edit($this->service, $id, $fields, JadwalAudit::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JadwalAudit::class);
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
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'periode_akademik'],
            ['field' => 'tanggal_awal_pengisian'],
            ['field' => 'tanggal_akhir_pengisian'],
            ['field' => 'tanggal_awal_penilaian'],
            ['field' => 'tanggal_akhir_penilaian'],
            ['field' => 'apakah_penilaian_mandiri', 'type' => 'select', 'options' => [0 => 'Tidak Aktif', 1 => 'Aktif'], 'selected' => 1],
            ['field' => 'apakah_audit_aktif', 'type' => 'select', 'options' => [0 => 'Tidak Aktif', 1 => 'Aktif'], 'selected' => 1],
        ];
    }
}
