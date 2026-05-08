<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;

class KegiatanKerjasamaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private KegiatanManagementService $kegiatanService,
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
        if (!$this->kerjasamaService->isIndukKerjasamaExists($id_parent)) {
            abort(404);
        }

        $header = [
            ['field' => 'id_unit_kerja', 'options' => [], 'searchable' => false, 'component' => true],
            ['field' => 'judul_kegiatan'],
            ['field' => 'id_mitra', 'component' => true, 'searchable' => false, 'options' => []],
            ['field' => 'id_induk_kerjasama', 'searchable' => false, 'options' => []],
            ['field' => 'tanggal_mulai_berlaku', 'component' => true],
            ['field' => 'anggaran', 'component' => true],
            ['field'=> 'action', 'component' => 'kegiatan-kerjasama'],
        ];

        foreach ($header as $index => $value) {
            if ($value['field'] == 'anggaran') {
                $header[$index]['label'] = 'Nilai Kontrak (Rp)';
                continue;
            } elseif ($value['field'] == 'tanggal_mulai_berlaku') {
                $header[$index]['label'] = 'Durasi Kegiatan';
                continue;
            }
            $header[$index]['label'] = __('kerjasama::kegiatan.' . $value['field']);
        }
        $viewData = [
            'sidebar' => Menu::kerjasamaSidebar($id_parent),
            'customCreateLink' => route('kerjasama.kegiatan.create') . "?id_kerjasama=" . $id_parent . "&backUrl=" . url()->current(),
            // 'lastBreadcrumb' => MenuHelper::getItem(Menu::class, "kerjasamaSidebar.$id_parent.data-kerjasama")
        ];

        $filter = $this->defineFilter();

        return WebController::index(
            $this->kegiatanService,
            $request,
            $header,
            $filter,
            $viewData,
            model: Kegiatan::class,
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
        return WebController::destroy($this->kegiatanService, $id_parent);
    }

    /**
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        return WebController::destroySome($this->kegiatanService, $request);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields(int $id)
    {
        $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();
        return [
            'informasi-kegiatan' => [
                'title' => 'Data Kegiatan',
                'subtitle' => 'Informasi Data Kegiatan Kerjasama',
                'edit_url' => route('kerjasama.kegiatan.edit', $id) . '?backUrl=' . url()->current(),
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
        $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();
        return [
            'id_unit_kerja' => [
                'options' => ['' => '-- Semua Unit Kerja --'] + $unitKerjaOptions,
                'hideLabel' => true,
            ],
        ];
    }
}
