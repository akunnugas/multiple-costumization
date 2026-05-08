<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Arr;
use DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Wilayah;
use Modules\Kerjasama\Helpers\ExportFormat;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Helpers\MitraImport;
use Modules\Kerjasama\Models\JenisDokumen;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\KriteriaMitra;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Models\PenanggungJawab;
use Modules\Kerjasama\Models\PihakPenanggungJawab;
use Modules\Kerjasama\Models\StatusKerjasama;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;

class KerjaSamaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private KerjasamaManagementService $service,
        private KegiatanManagementService $kegiatanService,
    ) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'id_unit_kerja', 'options' => [], 'component' => true],
            ['field' => 'judul_kerjasama'],
            ['field' => 'id_mitra', 'component' => true, 'options' => [], 'searchable' => false],
            ['field' => 'id_jenis_dokumen', 'options' => []],
            ['field' => 'nomor_dokumen'],
            ['field' => 'tanggal_mulai_berlaku', 'label' => 'Durasi Kerjasama', 'component' => true],
            ['field' => 'id_status_kerjasama', 'options' => [], 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => 'kerjasama-dokumen'],
        ];

        $countData = $this->service->getCountByStatus();

        $viewData = [
            'withExport' => true,
            'withImport' => true,
            'staticAlert' => $request->session()->get('staticAlert'),
        ];

        $filter = $this->defineFilter();

        return WebController::index(
            $this->service, 
            $request, 
            $header, 
            $filter, 
            $viewData, 
            Kerjasama::class,
            isUsingDefaultOrder: false
        );
    }


   public function dokumen($kegiatan)
    {
        try {
            $model = Kerjasama::with([
                'dokumenKerjasama'
            ])
            ->where('id', $kegiatan)
            ->whereNull('id_parent')
            ->firstOrFail();
            
            $model->realisasi = $this->kegiatanService->getTotalRealisasi($model->id);
            $model->dokumenKerjasama = $model->dokumenKerjasama->map(function ($dokumenKerjasama) {
                return [
                    ...$dokumenKerjasama->toArray(),
                    ...$dokumenKerjasama->dokumen->toArray(),
                    'nama_dokumen' => $dokumenKerjasama->dokumen->nama_dokumen,
                    'lampiran' => $dokumenKerjasama->dokumen->lastVersionTemporaryUrl(),
                    'ukuran' => $dokumenKerjasama->dokumen->lastVersionSize
                ];
            });

            if ($model->dokumenKerjasama->count() > 0) {
                $lampiranUrl = $model->dokumenKerjasama->first()['lampiran'];
                return redirect($lampiranUrl);
            }

            throw new \Exception('No documents found');

        } catch (\Throwable $th) {
            return new \Illuminate\Http\Response(['error' => 'Not found'], 404);
        }
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        // return view('kerjasama::pages.mitra.create-form');
        return WebController::create($this->defineFormFields(), Kerjasama::class, viewBlade: "kerjasama::pages.kerjasama.create");
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), Kerjasama::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $data = $this->service->show($id);

        if (Error::isError($data)) {
            return $data->redirectBack();
        }

        $cards = [
            ...$this->defineFormFields(),
            'informasi-penanggung-jawab' => [
                'title' => 'Pihak Penanggung Jawab',
                'subtitle' => 'Informasi Pihak Penanggung Jawab Kerjasama',
                'items' => [
                    ['field' => 'id_pihak', 'component' => true],
                    ['field' => 'alamat'],
                ]
            ]
        ];

        $cards['informasi-kerjasama']['title'] = $data['judul_kerjasama'];
        $cards['informasi-kerjasama']['subtitle'] = 'Detail informasi terkait data kerjasama dan penanggung jawab';

        $viewaData = [
            'headerPenanggungJawab' => [
                ['field' => 'nama_penanggung_jawab', 'label' => __('kerjasama::penanggungjawab.nama_penanggung_jawab')],
                ['field' => 'nip', 'label' => __('kerjasama::penanggungjawab.nip')],
                ['field' => 'jabatan', 'label' => __('kerjasama::penanggungjawab.jabatan')],
                ['field' => 'email', 'label' => __('kerjasama::penanggungjawab.email')],
                ['field' => 'telepon', 'label' => __('kerjasama::penanggungjawab.telepon')],
            ],
            'headerDokumenKerjasama' => [
                ['field' => 'lampiran', 'component' => true]
            ],
            'sidebar' => Menu::kerjasamaSidebar($id)
        ];


        return WebController::show(
            $this->service,
            $id,
            $cards,
            [Kerjasama::class, PihakPenanggungJawab::class, PenanggungJawab::class],
            $viewaData,
            data: $data
        );
    }

    public function export(Request $request)
    {
        $unitKerjaOptions = $this->service->getUnitKerjaOptions();
        $header = [
            ['field' => 'nomor_dokumen'],
            ['field' => 'nomor_dokumen_mitra'],
            ['field' => 'id_jenis_dokumen'],
            ['field' => 'id_unit_kerja', 'options' => $unitKerjaOptions],
            ['field' => 'id_mitra'],
            ['field' => 'judul_kerjasama'],
            ['field' => 'deskripsi'],
            ['field' => 'id_sumber_dana'],
            ['field' => 'anggaran'],
            ['field' => 'tanggal_mulai_berlaku'],
            ['field' => 'tanggal_akhir_berlaku'],
            ['field' => 'id_status_kerjasama'],
        ];

        return WebController::export('Export Data Kerjasama.xlsx', $this->service, $request, $header, $this->defineFilter(), Mitra::class);
    }

    public function import(Request $request)
    {
        $request->validate([
            'import' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        // dd($request);

        try {
            $import = new MitraImport();
            Excel::import($import, $request->file('import'));

            return redirect()->back()->with('staticAlert', [
                'message' => $import->getImportSummaryHtml($import->getImportSummary()),
                'variant' => 'info',
                'type' => 'single'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('staticAlert', [
                    'message' => $e->getMessage(),
                    'variant' => 'info',
                    'type' => 'single'
                ]);
        }
    }


    public function exportFormat()
    {
        $jenisDokumenOptions = JenisDokumen::pluck('jenis_dokumen')->toArray();
        $kriteriaMitraOptions = KriteriaMitra::orderBy('klasifikasi_mitra', 'asc')
            ->pluck('klasifikasi_mitra')
            ->toArray();
        $jenisMitraOptions = [
            'PT' => 'Perguruan Tinggi',
            'INS' => 'Non Perguruan Tinggi'
        ];

        $tingkatMitraOptions = [
            'LOKAL' => 'Lokal',
            'REGIONAL' => 'Regional',
            'NASIONAL' => 'Nasional',
            'INTERNASIONAL' => 'Internasional'
        ];

        $provinsiOptions = Wilayah::query()
            ->where('level_wilayah', Wilayah::LEVEL_PROVINSI)
            ->where('kode_wilayah', '!=', '0')
            ->pluck('nama_wilayah')
            ->toArray();

        $header = [
            ['field' => 'jenis_mitra'],
            ['field' => 'nama_mitra'],
            ['field' => 'id_kriteria_mitra'],
            ['field' => 'tingkat_mitra'],
            ['field' => 'id_provinsi'],
            ['field' => 'judul_kerjasama'],
            ['field' => 'nomor_dokumen'],
            ['field' => 'id_jenis_dokumen'],
            ['field' => 'tanggal_mulai_berlaku1', 'format' => 'dd/mm/yyyy'],
            ['field' => 'tanggal_akhir_berlaku1', 'format' => 'dd/mm/yyyy'],
            ['field' => 'nama_pihak_1'],
            ['field' => 'jabatan_pihak_1'],
            ['field' => 'email_pihak_1'],
            ['field' => 'no_hp_pihak_1', 'format' => 'phone'],
            ['field' => 'nama_pihak_2'],
            ['field' => 'jabatan_pihak_2'],
            ['field' => 'email_pihak_2'],
            ['field' => 'no_hp_pihak_2', 'format' => 'phone'],
        ];

        $dropdowns = [
            'A' => [
                'options' => $jenisMitraOptions
            ],
            'D' => [
                'options' => $tingkatMitraOptions
            ],
            'C' => [
                'options' => $kriteriaMitraOptions
            ],
            'E' => [
                'options' => $provinsiOptions
            ],
            'H' => [
                'options' => $jenisDokumenOptions
            ],
        ];

        return Excel::download(
            new ExportFormat(
                [],
                $header,
                null,
                $dropdowns
            ),
            'Format Data Import Kerjasama.xlsx'
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), Kerjasama::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), Kerjasama::class);
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

    private function defineFilter(): array
    {
        return [
            'id_mitra' => [
                'options' => ['' => '-- Semua Mitra Kerjasama --'] + Mitra::options(),
                'hideLabel' => true,
            ],
            'id_status_kerjasama' => [
                'options' => ['' => '-- Semua Status Kerjasama --'] + StatusKerjasama::options(),
                'hideLabel' => true,
            ],
            'tanggal_akhir_berlaku' => [
                'hideLabel' => true,
                'options' => Kerjasama::getFilterExpiredOptions()
            ]
        ];
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        $unitKerjaOptions = $this->service->getUnitKerjaOptions();
        return [
            'informasi-kerjasama' => [
                'title' => 'Data Kerjasama',
                'subtitle' => 'Informasi Data Kerjasama',
                'edit_url' => url()->current() . '/edit',
                'items' => [
                    ['field' => 'nomor_dokumen'],
                    ['field' => 'nomor_dokumen_mitra'],
                    ['field' => 'id_jenis_dokumen'],
                    ['field' => 'id_unit_kerja', 'options' => $unitKerjaOptions],
                    ['field' => 'id_mitra', 'dynamicComponent' => 'kerjasama::fields.id_mitra'],
                    ['field' => 'judul_kerjasama'],
                    ['field' => 'deskripsi'],
                    ['field' => 'id_sumber_dana'],
                    ['field' => 'anggaran'],
                    ['field' => 'tanggal_mulai_berlaku'],
                    ['field' => 'tanggal_akhir_berlaku'],
                    ['field' => 'id_status_kerjasama', 'dynamicComponent' => 'kerjasama::fields.id_status_kerjasama'],
                    ['field' => 'hasil_pelaksanaan', 'dynamicComponent' => 'kerjasama::fields.textarea_break_line'],
                    // ['field' => 'id_dokumen'],                
                ]
            ]
        ];
    }
}
