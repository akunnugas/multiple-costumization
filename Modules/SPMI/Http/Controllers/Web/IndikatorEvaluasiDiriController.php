<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Services\IndikatorEvaluasiDiriManagementService;

class IndikatorEvaluasiDiriController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private IndikatorEvaluasiDiriManagementService $service)
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
            ['field' => 'nomor_indikator', 'order' => 'desc'],
            ['field' => 'nama_indikator_evaluasi_diri', 'is_tree_view' => true],
            ['field' => 'apakah_parent', 'label' => 'Butir Utama? (Parent)', 'component' => 'apakah_parent', 'searchable' => false],
            ['field' => 'action', 'component' => 'indikator_evaluasi_diri', 'searchable' => false]
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optPengisianPanduan = PengisianPanduan::getListDefaultSelfEvaluation();
        $idPengisianPanduan = $request->filter['id_pengisian_panduan'] ?? session('filter_spmi_led_iku_id_pengisian_panduan') ?? array_key_first($optPengisianPanduan);
        
        if (!empty($idPengisianPanduan)) {
            session(['filter_spmi_led_iku_id_pengisian_panduan' => $idPengisianPanduan]);
        }
        
        if (!isset($optPengisianPanduan[$idPengisianPanduan])) {
            $idPengisianPanduan = array_key_first($optPengisianPanduan);
            session(['filter_spmi_led_iku_id_pengisian_panduan' => $idPengisianPanduan]);
        }

        $filter = [
            'id_pengisian_panduan' => [
                'options' => $optPengisianPanduan,
                'label' => 'Panduan Pengisian',
                'selected' => $idPengisianPanduan
            ],
            'apakah_data_default' => [
                'options' => ['true' => 'Ya', 'false' => 'Tidak'],
                'label' => 'Kategori',
                'selected' => 'true',
                'hideView' => true
            ],
        ];

        $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);

        $viewData['title'] = 'Komponen Evaluasi Diri (ED) Utama';
        $viewData['subtitle'] = '';

        return WebController::index(
            service: $this->service,
            request: $request,
            header: $header,
            filter: $filter,
            viewData: $viewData
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-evaluasi-diri');
        return WebController::create($this->defineFormFields(), IndikatorEvaluasiDiri::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), IndikatorEvaluasiDiri::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        $viewData['sidebar'] = Menu::indicatorSelfEvaluationSidebar($id);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-evaluasi-diri');
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, IndikatorEvaluasiDiri::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-evaluasi-diri');
        return WebController::edit($this->service, $id, $this->defineFormFields(), IndikatorEvaluasiDiri::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), IndikatorEvaluasiDiri::class);
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
        $listPR = PengisianPanduan::getListSelfEvaluation();

        return [
            ['field' => 'id_pengisian_panduan', 'options' => $listPR],
            ['field' => 'id_parent', 'options' => IndikatorEvaluasiDiri::class],
            ['field' => 'nomor_indikator'],
            ['field' => 'nama_indikator_evaluasi_diri'],
            ['field' => 'deskripsi', 'control' => 'textarea'],
            ['field' => 'apakah_komentar', 'control' => 'switch'],
            ['field' => 'apakah_key_point', 'control' => 'switch'],
            ['field' => 'apakah_parent', 'control' => 'switch'],
            ['field' => 'apakah_aktif', 'control' => 'switch']
        ];
    }
}
