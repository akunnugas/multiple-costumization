<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\Evaluasi;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Services\EvaluasiKerjasamaManagementService;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;

class EvaluasiKerjasamaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private EvaluasiKerjasamaManagementService $evaluasiKerjasamaManagementService,
        private KerjasamaManagementService $kerjasamaService
    ) {}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function index(Request $request, $id_parent)
    {
        // cek apakah induk kerjasama ada, jika tidak ada maka throw 404
        // if (!$this->kerjasamaService->isIndukKerjasamaExists($id_parent)) {
        //     abort(404);
        // }

        $header = [
            ['field' => 'judul_evaluasi', 'options' => [], 'searchable' => false, 'component' => true, 'attributes' => ['class' => 'text-center'], 'styleAlign' => 'center'],
            ['field' => 'tipe_evaluasi', 'component' => true, 'attributes' => ['class' => 'text-center'], 'styleAlign' => 'center'],
            ['field' => 'is_published', 'component' => true, 'attributes' => ['class' => 'text-center'], 'styleAlign' => 'center'],
            ['field' => 'durasi', 'component' => true, 'attributes' => ['class' => 'text-center'], 'styleAlign' => 'center'],
            // ['field' => 'selesai', 'component' => true],
            ['field' => 'jumlah_pertanyaan', 'component' => true, 'attributes' => ['class' => 'text-center'], 'styleAlign' => 'center'],
            ['field' => 'jumlah_peserta', 'component' => true, 'attributes' => ['class' => 'text-center'], 'styleAlign' => 'center'],
            ['field' => 'action', 'component' => 'evaluasi'],

        ];

        foreach ($header as $index => $value) {
            $header[$index]['label'] = __('kerjasama::evaluasi.' . $value['field']);
        }


        $viewData = [
            'sidebar' => Menu::kerjasamaSidebar($id_parent),
            'customCreateLink' => route('kerjasama.evaluasi-kuesioner.create') . "?id_parent=" . $id_parent . "&backUrl=" . url()->previous(),
            // 'lastBreadcrumb' => MenuHelper::getItem(Menu::class, "kerjasamaSidebar.$id_parent.data-kerjasama")
        ];

        $filter = $this->defineFilter();

        return WebController::index(
            $this->evaluasiKerjasamaManagementService,
            $request,
            $header,
            $filter,
            $viewData,
            model: Evaluasi::class,
            customMethod: 'indexForKerjasama',
            customMethodParams: [
                $id_parent
            ],
            isUsingDefaultOrder: false
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $id_parent)
    {
        return WebController::destroy($this->evaluasiKerjasamaManagementService, $id_parent);
    }


     public function index2($id)
    {

        dd('test2');
        return WebController::show($this->evaluasiKerjasamaManagementService, $id);
    }
    public function show($id)
    {

        dd('test2');
        return WebController::show($this->evaluasiKerjasamaManagementService, $id);
    }

    /**
     * Duplicate the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function duplicate($id)
    {

        $duplicate = $this->evaluasiKerjasamaManagementService->duplicate($id);

        if (Error::isError($duplicate)) {
            return $duplicate->redirectBack();
        }

        return redirect()->back()->with('success', 'Berhasil duplikat data ');
    }

    /**
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->evaluasiKerjasamaManagementService, $request);
    }


    public function create()
    {
        // return view('kerjasama::pages.mitra.create-form');
        return WebController::create($this->defineFormFields(), Evaluasi::class, viewBlade: "kerjasama::pages.evaluasi.create");
    }


    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        // $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();
        return [
            'informasi-kegiatan' => [

                'title' => 'Evaluasi Kegiatan Kerjasama',
                'items' => [

                    ['field' => 'anggaran'],
                    ['field' => 'link_dokumentasi', 'dynamicComponent' => 'kerjasama::fields.link_dokumentasi'],
                ]
            ]
        ];
    }

    private function defineFilter()
    {
        return [
            'tipe_evaluasi' => [
                'type' => 'search',
                'options' => ['' => '-- Semua Tipe --'] + Evaluasi::TIPE_EVALUASI_OPTIONS,
                'hideLabel' => true,
            ],
            'is_published' => [
                'type' => 'search',
                'options' => [
                    '' => '-- Semua Status --',
                    '1' => 'Dipublikasikan',
                    'unpublished' => 'Draft',
                    'kadaluwarsa' => 'Kadaluwarsa',
                    'dijadwalkan' => 'Dijadwalkan',
                ],
                'hideLabel' => true,
                'value' => request('filter.is_published', ''),
            ],
        ];
    }
}
