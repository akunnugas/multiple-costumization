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
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\OpsiJawaban;
use Modules\Kerjasama\Models\PenanggungJawab;
use Modules\Kerjasama\Models\Pertanyaan;
use Modules\Kerjasama\Models\PihakPenanggungJawab;
use Modules\Kerjasama\Services\EvaluasiKerjasamaManagementService;
use Modules\Kerjasama\Services\JawabanManagementService;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;

class EvaluasiKuesionerController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private EvaluasiKerjasamaManagementService $service,
        private JawabanManagementService $jawabanService,

        private KerjasamaManagementService $kerjasamaService
    ) {}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function index(Request $request, $id_parent)
    {

        $header = [
            ['field' => 'judul_evaluasi', 'options' => [], 'searchable' => false, 'component' => true],
            ['field' => 'tipe_evaluasi', 'component' => true],
            ['field' => 'is_published', 'component' => true],
            ['field' => 'durasi', 'component' => true],
            // ['field' => 'selesai', 'component' => true],
            ['field' => 'jumlah_pertanyaan', 'component' => true],
            ['field' => 'jumlah_peserta', 'component' => true],
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
            $this->service,
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
        return WebController::destroy($this->service, $id_parent);
    }


    // TODO RAIS
    public function show($id)
    {
        $data = $this->service->show($id);
        
        if (Error::isError($data)) {
            return $data->redirectBack();
        }

        $cards = [
            ...$this->defineFormFields(),
            'informasi-kuesioner' => [
                'title' => 'Soal Kuesioner',
                'subtitle' => 'List Soal Kuesioner',
                'items' => [
                    // ['field' => 'id_pihak', 'component' => true],
                    ['field' => 'soal'],
                ]
            ],
            'peserta' => [
                'title' => 'Rekap Jawaban Kuesioner',
                'subtitle' => 'Rekap Jawaban Kuesioner',
                'items' => [
                    // ['field' => 'id_pihak', 'component' => true],
                    ['field' => 'soal'],
                ]
            ]
        ];


        $cards['informasi-kerjasama']['title'] = $data['judul_evaluasi'];
        $cards['informasi-kerjasama']['subtitle'] = 'Detail informasi terkait evaluasi';


        foreach ($cards['informasi-kerjasama']['items'] as &$item) {
            if (isset($item['field']) && $item['field'] == 'is_published') {
                $item['text'] = $this->service->getPublishStatusText($data);
                $item['component'] = false;
            }
        }
        $viewaData = [
            'headerPenanggungJawab' => [
                ['field' => 'nama_penanggung_jawab', 'label' => __('kerjasama::penanggungjawab.nama_penanggung_jawab')],
                ['field' => 'nip', 'label' => __('kerjasama::penanggungjawab.nip')],
                ['field' => 'jabatan', 'label' => __('kerjasama::penanggungjawab.jabatan')],
                ['field' => 'email', 'label' => __('kerjasama::penanggungjawab.email')],
                ['field' => 'telepon', 'label' => __('kerjasama::penanggungjawab.telepon')],
            ],
            // 'headerDokumenKerjasama' => [
            //     ['field' => 'lampiran', 'component' => true]
            // ],
            'sidebar' => Menu::kerjasamaSidebar($data['model_id'], $id)
        ];

        return WebController::show(
            $this->service,
            $id,
            $cards,
            [Evaluasi::class, Pertanyaan::class, OpsiJawaban::class],
            $viewaData,
            data: $data
        );
    }


    private function defineFormFields()
    {
        return [
            'informasi-kerjasama' => [
                'title' => 'Data Evaluasi',
                'subtitle' => 'Informasi Evaluasi',
                'edit_url' => url()->current() . '/edit',
                'items' => [
                    [
                        'field' => 'judul_evaluasi',
                    ],
                    [
                        'field' => 'tipe_evaluasi',

                    ],
                    [
                        'field' => 'is_published',
                        'component' => true
                    ],
                    [
                        'field' => 'mulai',

                    ],
                    [
                        'field' => 'selesai',

                    ],
                ],
            ],
        ];
    }

    private function defineFilter()
    {
        return [];
    }
}
