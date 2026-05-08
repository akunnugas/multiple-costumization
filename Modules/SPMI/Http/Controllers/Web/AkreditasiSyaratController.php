<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\AkreditasiSyarat;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\AkreditasiSyaratManagementService;

class AkreditasiSyaratController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private AkreditasiSyaratManagementService $service)
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
            ['field' => 'id_penilaian_matriks', 'order' => true, 'definer' => true],
            ['field' => 'id_akreditasi_peringkat'],
            ['field' => 'jenis_syarat_akreditasi'],
            ['field' => 'nilai_syarat_akreditasi', 'searchable' => false],
            ['field' => 'apakah_data_default', 'label' => 'Sumber Data', 'component' => "apakah_data_default", 'searchable' => false],
            ['field' => 'action', 'component' => 'akreditasi_syarat_action', 'searchable' => false]
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optPenilaianPanduan = PenilaianPanduan::options();

        $idPenilaianPanduan = $request->filter['id_penilaian_panduan'] ?? session('filter_penilaian_panduan') ?? array_key_first($optPenilaianPanduan);

        if (!empty($idPenilaianPanduan)) {
            session(['filter_penilaian_panduan' => $idPenilaianPanduan]);
        }
        if (
            !isset($optPenilaianPanduan[$idPenilaianPanduan])
        ) {
            $idPenilaianPanduan = array_key_first($optPenilaianPanduan);
            session(['filter_penilaian_panduan' => $idPenilaianPanduan]);
        }

        $filter = [
            'filter_penilaian_panduan' => [
                'options' => $optPenilaianPanduan,
                'label' => 'Pilih Panduan Penilaian',
                'selected' => $idPenilaianPanduan
            ],
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData, model: AkreditasiSyarat::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), AkreditasiSyarat::class);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-syarat');
        return WebController::create($this->defineFormFields(), AkreditasiSyarat::class, $viewData);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $cards[] = ['field' => 'apakah_data_default', 'control' => 'checkbox', 'disabled' => true];

        $akreditasiStandar = AkreditasiSyarat::find($id);
        if ($akreditasiStandar->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.akreditasi-syarat');
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, AkreditasiSyarat::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), AkreditasiSyarat::class);
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
            ['field' => 'id_penilaian_panduan', 'options' => PenilaianPanduan::class, 'selected' => session('filter_penilaian_panduan')],
            ['field' => 'id_penilaian_matriks', 'options' => PenilaianMatriks::class],
            ['field' => 'id_akreditasi_peringkat', 'options' => AkreditasiPeringkat::class],
            ['field' => 'jenis_syarat_akreditasi', 'options' => AkreditasiSyarat::TYPES],
            ['field' => 'nilai_syarat_akreditasi', 'maxlength' => 4, 'unique' => true, 'type' => 'number'],
        ];
    }
}
