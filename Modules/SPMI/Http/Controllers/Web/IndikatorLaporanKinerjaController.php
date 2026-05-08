<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Services\IndikatorLaporanKinerjaManagementService;

class IndikatorLaporanKinerjaController extends Controller
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
            ['field' => 'nama_indikator_laporan_kinerja', 'is_tree_view' => true],
            ['field' => 'apakah_parent', 'label' => 'Butir Utama? (Parent)', 'component' => 'apakah_parent', 'searchable' => false],
            ['field' => 'sumber_data', 'options' => IndikatorLaporanKinerja::getDataSource(), 'searchable' => false],
            ['field' => 'action', 'component' => 'indikator_laporan_kinerja', 'searchable' => false]
        ];

        $optPengisianPanduan = PengisianPanduan::getListDefaultIndicatorPerformanceReport();
        $idPengisianPanduan = $request->filter['id_pengisian_panduan'] ?? session('filter_spmi_lk_iku_id_pengisian_panduan') ?? array_key_first($optPengisianPanduan);

        if (!empty($idPengisianPanduan)) {
            session(['filter_spmi_lk_iku_id_pengisian_panduan' => $idPengisianPanduan]);
        }

        if (!isset($optPengisianPanduan[$idPengisianPanduan])) {
            $idPengisianPanduan = array_key_first($optPengisianPanduan);
            session(['filter_spmi_lk_iku_id_pengisian_panduan' => $idPengisianPanduan]);
        }

        $filter = [
            'id_pengisian_panduan' => [
                'options' => $optPengisianPanduan,
                'label' => 'Panduan Pengisian',
                'selected' => $idPengisianPanduan
            ],
            'apakah_data_default' => [
                'options' => ["true" => "true"],
                'label' => 'Kategori',
                'selected' => "true",
                'hideView' => true
            ],
        ];

        $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');
        $viewData['title'] = 'Tabel Laporan Kinerja (LK) Utama';
        $viewData['subtitle'] = '';

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();

        $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);

        $viewData['sidebar'] = Menu::indicatorSidebar($id);

        // $viewData['generateTable'] = $this->service->generateTable($id, isPreview: true);
        $viewData['isDetailV2'] = true;
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-laporan-kinerja');

        return WebController::show($this->service, $id, $cards, IndikatorLaporanKinerja::class, $viewData);
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

        return [
            ['field' => 'id_pengisian_panduan', 'options' => $listPR],
            ['field' => 'id_parent', 'options' => $listData],
            ['field' => 'nomor_indikator'],
			['field' => 'nama_indikator_laporan_kinerja'],
			['field' => 'deskripsi', 'control' => 'textarea'],
			['field' => 'informasi', 'control' => 'textarea'],
			// ['field' => 'jenis_form', 'options' => $listFormType],
            // ['field' => 'apakah_layout_fixed', 'control' => 'switch'],
            ['field' => 'apakah_menggunakan_kategori', 'control' => 'switch'],
            // ['field' => 'apakah_memasukkan_kategori_manual', 'control' => 'switch'],
            ['field' => 'apakah_menggunakan_ts', 'control' => 'switch'],
            // ['field' => 'apakah_subfooter', 'control' => 'switch'],
			['field' => 'sumber_data', 'options' => $listDataSource],
			['field' => 'deskripsi_sumber_data', 'control' => 'textarea'],
            ['field' => 'apakah_import_excel', 'control' => 'switch'],
            ['field' => 'dapat_dilihat_pada_laporan', 'control' => 'switch'],
            ['field' => 'dapat_lihat_nama_pada_laporan', 'control' => 'switch'],
            // ['field' => 'jenis_layout', 'options' => $listLayoutType],
            // ['field' => 'apakah_data_default', 'control' => 'switch'],
            ['field' => 'apakah_aktif', 'control' => 'switch', 'label' => 'Apakah Aktif']
        ];
    }
}
