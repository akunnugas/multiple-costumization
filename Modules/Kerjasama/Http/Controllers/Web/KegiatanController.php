<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Arr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\UnitKerja;
use Modules\Kerjasama\Helpers\ExportFormat;
use Modules\Kerjasama\Helpers\KegiatanImport;
use Modules\Kerjasama\Models\JenisKegiatan;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\Mitra;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;

class KegiatanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private KegiatanManagementService $service,
        private KerjasamaManagementService $kerjasamaService
    ) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'id_unit_kerja', 'options' => [], 'searchable' => false, 'component' => true],
            ['field' => 'judul_kegiatan'],
            ['field' => 'id_mitra', 'component' => true, 'searchable' => false, 'options' => []],
            ['field' => 'id_induk_kerjasama', 'searchable' => false, 'options' => []],
            ['field' => 'tanggal_mulai_berlaku', 'label' => 'Durasi Kegiatan', 'component' => true],
            ['field' => 'anggaran', 'component' => true, 'label' => 'Nilai Kontrak (Rp)'],
        ];

        $filter = $this->defineFilter();

        $viewData = [
            'withImport' => true,
            'staticAlert' => $request->session()->get('staticAlert')
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            $filter,
            $viewData,
            model: Kegiatan::class,
            isUsingDefaultOrder: false
        );
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

        // jika kegiatan merupakan joint degree atau double degree maka tambah card baru
        [$isJoinDegreeOrDoubleDegree, $namaBentukKegiatan] = $this->service->isKegiatanJoinOrDoubleDegree($data->toArray());
        $cardPelaksanaKegiatan = [];
        if ($isJoinDegreeOrDoubleDegree) {
            $cardPelaksanaKegiatan['pelaksana-kegiatan'] = [
                'title' => 'Mahasiswa Pelaksana ' . $namaBentukKegiatan,
                'subtitle' => 'Informasi Mahasiswa Pelaksana Kegiatan',
                'icon' => 'graduation-hat-02',
                'items' => [
                    ['field' => '_', 'hidden' => true],
                ]
            ];
        }

        $cards = [
            ...$this->defineFormFields(),
            ...$cardPelaksanaKegiatan,
            'informasi-penanggung-jawab' => [
                'title' => 'Pihak Penanggung Jawab',
                'subtitle' => 'Informasi Pihak Penanggung Jawab Kerjasama',
                'icon' => 'users',
                'items' => [
                    ['field' => 'id_pihak', 'component' => true],
                    ['field' => 'alamat'],
                ]
            ]
        ];

        $cards['informasi-kegiatan']['title'] = $data['judul_kegiatan'];
        $cards['informasi-kegiatan']['subtitle'] = "Detail informasi terkait data kegiatan dan penanggung jawab";

        $viewaData = [
            'headerPenanggungJawab' => [
                ['field' => 'nama_penanggung_jawab', 'label' => __('kerjasama::penanggungjawab.nama_penanggung_jawab')],
                ['field' => 'nip', 'label' => __('kerjasama::penanggungjawab.nip')],
                ['field' => 'jabatan', 'label' => __('kerjasama::penanggungjawab.jabatan')],
                ['field' => 'email', 'label' => __('kerjasama::penanggungjawab.email')],
                ['field' => 'telepon', 'label' => __('kerjasama::penanggungjawab.telepon')],
            ],
            'headerDokumenKegiatan' => [
                ['field' => 'lampiran', 'component' => true]
            ],
            'headerPelaksanaKegiatan' => [
                ['field' => 'id_mahasiswa', 'label' => __('kerjasama::pelaksana_kegiatan.id_mahasiswa')],
                ['field' => 'unit_kerja', 'label' => __('kerjasama::pelaksana_kegiatan.unit_kerja')],
                ['field' => 'program_studi', 'label' => __('kerjasama::pelaksana_kegiatan.program_studi')],
                ['field' => 'informasi_tambahan', 'label' => __('kerjasama::pelaksana_kegiatan.informasi_tambahan')]
            ]
        ];

        return WebController::show(
            $this->service,
            $id,
            $cards,
            Kegiatan::class,
            $viewaData,
            data: $data
        );
    }

    public function report($id)
    {
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

        return WebController::show(
            $this->service,
            $id,
            $cards,
            Kegiatan::class,
            [
                'universitas' => Arr::first(UnitKerja::optionByType([UnitKerja::UNIVERSITY]))
            ],
            'kerjasama::reports.kerjasama-report'
        );
    }


    public function import(Request $request)
    {
        $request->validate([
            'import' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new KegiatanImport();
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

        $kegiatanOptions = JenisKegiatan::join('kerjasama.bentuk_kegiatan', 'jenis_kegiatan.id', '=', 'bentuk_kegiatan.id_jenis_kegiatan')
            ->whereNull('jenis_kegiatan.waktu_dihapus')
            ->whereNull('bentuk_kegiatan.waktu_dihapus')
            ->select(DB::raw("CONCAT(jenis_kegiatan.nama_jenis_kegiatan, ' - ', bentuk_kegiatan.nama_bentuk_kegiatan) as nama"))
            ->pluck('nama')
            ->toArray();
        $mitraOptions = Kerjasama::whereNull('waktu_dihapus')
            ->select(DB::raw("CONCAT(id, ' - ', judul_kerjasama) as judul_kerjasama"))
            ->pluck('judul_kerjasama')
            ->toArray();
        $header = [
            ['field' => 'judul_kegiatan'],
            ['field' => 'judul_kerjasama'],
            ['field' => 'bentuk_kegiatan'],
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
            'B' => [
                'options' => $mitraOptions
            ],
            'C' => ['options' => $kegiatanOptions]

        ];

        return Excel::download(
            new ExportFormat(
                [],
                $header,
                null,
                $dropdowns
            ),
            'Export Format Kegiatan.xlsx'
        );
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
        $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();
        return [
            'informasi-kegiatan' => [
                'title' => 'Data Kegiatan',
                'subtitle' => 'Informasi Data Kegiatan Kerjasama',
                'edit_url' => url()->current() . '/edit',
                'items' => [
                    ['field' => 'id_induk_kerjasama', 'dynamicComponent' => 'kerjasama::fields.id_induk_kerjasama'],
                    ['field' => 'nomor_dokumen'],
                    ['field' => 'nomor_dokumen_mitra'],
                    ['field' => 'id_unit_kerja', 'options' => $unitKerjaOptions],
                    ['field' => 'id_mitra', 'dynamicComponent' => 'kerjasama::fields.id_mitra'],
                    ['field' => 'judul_kegiatan'],
                    ['field' => 'id_bentuk_kegiatan'],
                    ['field' => 'id_sasaran_kinerja'],
                    ['field' => 'id_indikator_sasaran'],
                    ['field' => 'tanggal_mulai_berlaku'],
                    ['field' => 'tanggal_akhir_berlaku'],
                    ['field' => 'ruang_lingkup', 'dynamicComponent' => 'kerjasama::fields.textarea_break_line'], // ruang lingkup
                    ['field' => 'hasil_pelaksanaan', 'dynamicComponent' => 'kerjasama::fields.textarea_break_line'],
                    ['field' => 'anggaran'],
                    ['field' => 'link_dokumentasi', 'dynamicComponent' => 'kerjasama::fields.link_dokumentasi'],
                ]
            ]
        ];
    }

    private function defineFilter()
    {
        $indukKerjasamaOptions = $this->kerjasamaService->getIndukKerjasamaOptions(withFilterStatusKerjasama: false);
        $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();
        return [
            'id_mitra' => [
                'options' => ['' => '-- Semua Mitra Kerjasama --'] + Mitra::options(),
                'hideLabel' => true,
            ],
            'id_induk_kerjasama' => [
                'options' => ['' => '-- Semua Induk Kerjasama --'] + $indukKerjasamaOptions,
                'hideLabel' => true,
            ],
            'id_unit_kerja' => [
                'options' => ['' => '-- Semua Unit Kerja --'] + $unitKerjaOptions,
                'hideLabel' => true,
            ],
        ];
    }
}
