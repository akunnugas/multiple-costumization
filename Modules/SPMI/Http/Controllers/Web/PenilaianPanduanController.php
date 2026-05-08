<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\PenilaianPanduanManagementService;

class PenilaianPanduanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PenilaianPanduanManagementService $service)
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
            ['field' => 'kode_penilaian_panduan'],
			['field' => 'nama_penilaian_panduan'],
			['field' => 'nama_singkat_penilaian_panduan'],
            ['field' => 'nama_laporan_kinerja'],
            ['field' => 'nama_panduan_evaluasi_diri'],
			['field' => 'apakah_aktif_penilaian_panduan', 'component' => 'apakah_aktif', 'searchable' => false],
            ['field' => 'action', 'component' => "penilaian_panduan_action", 'searchable' => false],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-panduan');
        return WebController::create($this->defineFormFields(), PenilaianPanduan::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), PenilaianPanduan::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $viewData['sidebar'] = Menu::penilaianPanduanSidebar($id);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-panduan');
        $viewData['isDetailV2'] = true;

        $penilaianPanduan = PenilaianPanduan::find($id);
        if ($penilaianPanduan->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        if ($penilaianPanduan->apakah_menggunakan_peringkat) {
            if (isset($viewData['sidebar']['items'][1]['items'])) {
                $viewData['sidebar']['items'][1]['items'] = array_values(array_filter(
                    $viewData['sidebar']['items'][1]['items'],
                    function ($item) {
                        return !(isset($item['path']) && str_contains($item['path'], '/akreditasi-status'));
                    }
                ));
            }
        } else {
            if (isset($viewData['sidebar']['items'][1]['items'])) {
                $viewData['sidebar']['items'][1]['items'] = array_values(array_filter(
                    $viewData['sidebar']['items'][1]['items'],
                    function ($item) {
                        return !(isset($item['path']) && str_contains($item['path'], '/akreditasi-peringkat'));
                    }
                ));
            }
        }

        return WebController::show($this->service, $id, $cards, PenilaianPanduan::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-panduan');
        return WebController::edit($this->service, $id, $this->defineFormFields(), PenilaianPanduan::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), PenilaianPanduan::class);
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

        return [
            ['field' => 'kode_penilaian_panduan', 'label' => 'Kode Panduan Penilaian'],
			['field' => 'nama_penilaian_panduan', 'label' => 'Nama Panduan Penilaian'],
			['field' => 'nama_singkat', 'label' => 'Nama Singkat'],
			['field' => 'tanggal_edisi'],
            ['field' => 'id_jenis_standar'],
            ['field' => 'id_laporan_kinerja', 'options' => $listPR],
            ['field' => 'id_jenjang_pendidikan', 'label' => 'Jenjang Pendidikan'],
			['field' => 'deskripsi', 'control' => 'textarea'],
			['field' => 'dapat_lihat_skor_akhir', 'control' => 'switch'],
			['field' => 'apakah_aktif', 'control' => 'switch'],
			['field' => 'apakah_target_aktif', 'control' => 'switch'],
            ['field' => 'id_dokumen'],
        ];
    }
}
