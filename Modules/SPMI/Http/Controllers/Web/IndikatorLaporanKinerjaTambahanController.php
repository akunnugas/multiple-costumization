<?php

namespace Modules\SPMI\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\IndikatorLaporanKinerjaManagementService;

class IndikatorLaporanKinerjaTambahanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorLaporanKinerjaManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'nomor_indikator'],
            ['field' => 'nama_indikator_laporan_kinerja', 'label' => 'Nama Indikator LK Tambahan', 'is_tree_view' => true],
            ['field' => 'apakah_parent', 'label' => 'Butir Utama? (Parent)', 'component' => 'apakah_parent', 'searchable' => false],
            ['field' => 'action', 'component' => 'indikator_laporan_kinerja_tambahan', 'searchable' => false]
        ];

        $optPengisianPanduan = PengisianPanduan::getListIndicatorPerformanceReport();
        $idPengisianPanduan = $request->filter['id_pengisian_panduan'] ?? session('filter_spmi_lk_ikt_id_pengisian_panduan') ?? array_key_first($optPengisianPanduan);

        if (!empty($idPengisianPanduan)) {
            session(['filter_spmi_lk_ikt_id_pengisian_panduan' => $idPengisianPanduan]);
        }

        if (!isset($optPengisianPanduan[$idPengisianPanduan])) {
            $idPengisianPanduan = array_key_first($optPengisianPanduan);
            session(['filter_spmi_lk_ikt_id_pengisian_panduan' => $idPengisianPanduan]);
        }

        $filter = [
            'id_pengisian_panduan' => [
                'options' => $optPengisianPanduan,
                'label' => 'Panduan Pengisian',
                'selected' => $idPengisianPanduan
            ],
            'apakah_data_default' => [
                'options' => ["false" => "false"],
                'label' => 'Kategori',
                'selected' => "false",
                'hideView' => true
            ],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');
        $viewData['title'] = 'Tabel Laporan Kinerja (LK) Tambahan';
        $viewData['subtitle'] = '';

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');
        return WebController::create($this->defineFormFields(), IndikatorLaporanKinerja::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');
        return WebController::edit($this->service, $id, $this->defineFormFields(), IndikatorLaporanKinerja::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), IndikatorLaporanKinerja::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        WebController::validate($id);

        $cards = $this->defineFormFields();

        $viewData['sidebar'] = Menu::indicatorLKTambahan($id);
        // $viewData['generateTable'] = $this->service->generateTable($id, isPreview: true);
        $viewData['isDetailV2'] = true;
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');

        $data = $this->service->show($id);
        $mappingLK = MappingLK::where('spmi.mapping_lk.id_indikator_laporan_kinerja', $id)
            ->join('spmi.audit_periode as ap', 'ap.id', '=', 'spmi.mapping_lk.id_audit_periode')
            ->join('core.unit_kerja as uk', 'uk.id', '=', 'spmi.mapping_lk.id_unit')
            ->where('uk.waktu_dihapus', null)
            ->where('ap.waktu_dihapus', null)
            ->select('spmi.mapping_lk.*')
            ->get();

        $program_studi = $mappingLK->pluck('id_unit')->unique()->toArray();
        $data->program_studi = array_values($program_studi);
        $periode_ami = $mappingLK->pluck('id_audit_periode')->unique()->toArray();
        $data->periode_ami = array_values($periode_ami);

        return WebController::show(
            service: $this->service,
            id: $id, cards: $cards,
            model: IndikatorLaporanKinerja::class,
            viewData: $viewData,
            data: $data
        );
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), IndikatorLaporanKinerja::class);
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
        $listPR = PengisianPanduan::getListIndicatorPerformanceReport();
        $listData = IndikatorLaporanKinerja::options();
        $listFormType = IndikatorLaporanKinerja::getFormType();
        $listLayoutType = IndikatorLaporanKinerja::getLayoutType();
        $listDataSource = IndikatorLaporanKinerja::getDataSource();

        $units = UnitKerja::select('id', 'nama_unit', 'id_jenjang_pendidikan')
            ->with('jenjang')
            ->whereIn('jenis_unit', [UnitKerja::UNIT_NON_PRODI, UnitKerja::STUDY_PROGRAM])
            ->get();
        $units = $units->map(function ($item) {
            $jenjang = '';
            if (!empty($item->jenjang)) {
                $jenjang = $item->jenjang->kode_jenjang . ' - ';
            }

            return [
                'id' => $item->id,
                'nama_unit' => $jenjang . $item->nama_unit
            ];
        })->toArray();
        $units = array_column($units, 'nama_unit', 'id');

        $periods = AuditPeriode::select('id', 'tahun_audit')
            ->orderBy('tahun_audit', 'desc')
            ->pluck('tahun_audit', 'id')
            ->toArray();

        return [
            ['field' => 'id_pengisian_panduan', 'options' => $listPR, 'label' => 'Panduan Pengisian'],
            ['field' => 'id_parent', 'options' => $listData, 'label' => 'Butir Parent'],
            ['field' => 'nomor_indikator'],
			['field' => 'nama_indikator_laporan_kinerja'],
			['field' => 'deskripsi', 'control' => 'textarea'],
			['field' => 'informasi', 'control' => 'textarea'],
			// ['field' => 'jenis_form', 'options' => $listFormType],
            // ['field' => 'apakah_layout_fixed', 'control' => 'switch'],
            // ['field' => 'apakah_menggunakan_kategori', 'control' => 'switch'],
            // ['field' => 'apakah_memasukkan_kategori_manual', 'control' => 'switch'],
            // ['field' => 'apakah_menggunakan_ts', 'control' => 'switch'],
            // ['field' => 'apakah_subfooter', 'control' => 'switch'],
			// ['field' => 'sumber_data', 'options' => $listDataSource],
			// ['field' => 'deskripsi_sumber_data', 'control' => 'textarea'],
            // ['field' => 'apakah_import_excel', 'control' => 'switch'],
            ['field' => 'apakah_parent', 'options' => [true => 'Ya', false => 'Tidak'], 'label' => 'Apakah Parent'],
            ['field' => 'dapat_dilihat_pada_laporan', 'options' => [true => 'Ya', false => 'Tidak'], 'label' => 'Dapat Dilihat pada Laporan'],
            ['field' => 'dapat_lihat_nama_pada_laporan', 'options' => [true => 'Ya', false => 'Tidak'], 'label' => 'Dapat Lihat Nama pada Laporan'],
            // ['field' => 'jenis_layout', 'options' => $listLayoutType],
            // ['field' => 'apakah_data_default', 'control' => 'switch'],
            ['field' => 'apakah_aktif', 'control' => 'switch'],
            ['field' => 'periode_ami', 'options' => $periods],
            ['field' => 'program_studi', 'options' => $units],
        ];
    }
}
