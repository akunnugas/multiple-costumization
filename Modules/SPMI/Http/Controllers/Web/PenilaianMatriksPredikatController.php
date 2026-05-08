<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use Modules\SPMI\Services\PenilaianMatriksManagementService;
use Modules\SPMI\Services\PenilaianMatriksPredikatManagementService;

class PenilaianMatriksPredikatController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PenilaianMatriksPredikatManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param int $PenilaianMatriksId
     * @return Renderable
     */
    public function index(Request $request, $PenilaianMatriksId)
    {
        $PenilaianMatriks = (new PenilaianMatriksManagementService)->show($PenilaianMatriksId);
        $isNotQualitative = false;
        if ($PenilaianMatriks->jenis_penilaian !== PenilaianMatriks::TYPE_QUALITATIVE) {
            $isNotQualitative = true;
        }

        $skorMatriksPredikat = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $PenilaianMatriks->id_penilaian_panduan)
            ->orderBy('nilai', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->nilai . ' - ' . $item->deskripsi];
            })
            ->toArray();

        $header = [
            ['field' => 'id_skor_matriks_predikat_penilaian', 'options' => $skorMatriksPredikat, 'label' => 'Nilai'],
            ['field' => 'deskripsi', 'control' => 'textarea', 'component' => true],
            ['field' => 'apakah_nonaktif', 'label' => 'Status Aktif', 'component' => 'apakah_switch'],
        ];

        if ($isNotQualitative) {
            $header[] = ['field' => 'kriteria', 'maxlength' => '100'];
            $header[] = ['field' => 'rumus_penilaian', 'maxlength' => '100'];
        }

        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-matriks-ikt');
        $viewData['isDetailV2'] = true;
        $this->service->setParentResourceId($PenilaianMatriksId);

        $viewData['sidebar'] = Menu::PenilaianMatriksIktSidebar($PenilaianMatriksId);
        if (!$request->routeIs('spmi.penilaian-matriks-ikt.penilaian-matriks-predikat.*') || $PenilaianMatriks->kategori_penilaian == PenilaianMatriks::CATEGORY_ELEMENT) {
            $viewData['sidebar'] = Menu::PenilaianMatriksSidebar($PenilaianMatriksId);
            $request->merge([
                'permission' => [
                    'post' => false,
                    'put' => false,
                    'delete' => false,
                    'custom' => false,
                    'get' => true,
                ]
            ]);
        }

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param int $PenilaianMatriksId
     * @return Renderable
     */
    public function store(Request $request, $PenilaianMatriksId)
    {
        $request->merge(['id_penilaian_matriks' => $PenilaianMatriksId]);
        $fields = $this->defineFormFields();

        // add registration period id to fields
        array_unshift($fields, ['field' => 'id_penilaian_matriks']);

        return WebController::store($this->service, $request, $fields, PenilaianMatriksPredikat::class);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create($penilaianMatriksId)
    {
        $fields = $this->defineFormFields();
        $penilaianMatriks = (new PenilaianMatriksManagementService)->show($penilaianMatriksId);
        $fields[0]['options'] = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $penilaianMatriks->id_penilaian_panduan)
            ->orderBy('nilai', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->nilai . ' - ' . $item->deskripsi];
            })
            ->toArray();

        if ($penilaianMatriks->jenis_penilaian !== PenilaianMatriks::TYPE_QUALITATIVE || !$penilaianMatriks->apakah_data_default) {
            unset($fields[3]); // remove kriteria
            unset($fields[4]); // remove rumus_penilaian
        }
        return WebController::create($fields, PenilaianMatriksPredikat::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($idPenilaianMatriks, $idSkorMatriksPredikat)
    {
        $cards = $this->defineFormFields();
        $penilaianMatriks = (new PenilaianMatriksManagementService)->show($idPenilaianMatriks);
        $cards[0]['options'] = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $penilaianMatriks->id_penilaian_panduan)
            ->orderBy('nilai', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->nilai . ' - ' . $item->deskripsi];
            })
            ->toArray();
        if ($penilaianMatriks->jenis_penilaian !== PenilaianMatriks::TYPE_QUALITATIVE || !$penilaianMatriks->apakah_data_default) {
            unset($cards[3]); // remove kriteria
            unset($cards[4]); // remove rumus_penilaian
        }
        return WebController::edit($this->service, $idSkorMatriksPredikat, $cards, PenilaianMatriksPredikat::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($idPenilaianMatriks, $idSkorMatriksPredikat)
    {
        $cards = $this->defineFormFields();
        $viewData['sidebar'] = Menu::PenilaianMatriksIktSidebar($idPenilaianMatriks);
        $viewData['isDetailV2'] = true;

        $matrik = PenilaianMatriks::findOrFail($idPenilaianMatriks);
        $cards[0]['options'] = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $matrik->id_penilaian_panduan)
            ->orderBy('nilai', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->nilai . ' - ' . $item->deskripsi];
            })
            ->toArray();

        if ($matrik->apakah_data_default) {
            // set permission in request
            $permission = request()->permission;
            $permission['put'] = false;
            request()->merge(['permission' => $permission]);
        }

        if ($matrik->jenis_penilaian !== PenilaianMatriks::TYPE_QUALITATIVE || !$matrik->apakah_data_default) {
            unset($cards[3]); // remove kriteria
            unset($cards[4]); // remove rumus_penilaian
        }

        return WebController::show($this->service, $idSkorMatriksPredikat, $cards, PenilaianMatriksPredikat::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @param int $PenilaianMatriksId
     * @return Renderable
     */
    public function update(Request $request, $id, $PenilaianMatriksId)
    {
        return WebController::update($this->service, $PenilaianMatriksId, $request, $this->defineFormFields(), PenilaianMatriksPredikat::class);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $idSkorMatriksPredikat)
    {
        return WebController::destroy($this->service, $idSkorMatriksPredikat);
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
            ['field' => 'id_skor_matriks_predikat_penilaian', 'label' => 'Nilai', 'control' => 'select', 'options' => [], 'required' => true],
            ['field' => 'deskripsi', 'control' => 'textarea'],
            ['field' => 'apakah_nonaktif', 'label' => 'Status Aktif', 'control' => 'switch-invert'],
            ['field' => 'kriteria', 'maxlength' => 100],
            ['field' => 'rumus_penilaian'],
        ];
    }
}
