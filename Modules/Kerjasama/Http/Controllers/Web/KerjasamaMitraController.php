<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\StatusKerjasama;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Modules\Kerjasama\Services\MitraManagementService;

class KerjasamaMitraController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private KerjasamaManagementService $service,
        private MitraManagementService $mitraService
    )
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request, $id_mitra)
    {

        if (!$this->mitraService->isExists($id_mitra)) {
            abort(404);
        }

        $header = [
            ['field' => 'id_unit_kerja', 'options' => [], 'component' => true],
            ['field' => 'judul_kerjasama'],
            ['field' => 'id_mitra', 'component' => true, 'options' => [], 'searchable' => false],
            ['field' => 'id_jenis_dokumen', 'options' => []],
            ['field' => 'nomor_dokumen'],
            ['field' => 'tanggal_mulai_berlaku', 'label' => 'Durasi Kerjasama', 'component' => true],
            ['field' => 'id_status_kerjasama', 'options' => [], 'component' => true, 'searchable' => false],
        ];

        foreach ($header as $index => $value) {
            $header[$index]['label'] = __('kerjasama::data_kerjasama.' . $value['field']);
        }

        $filter = $this->defineFilter();
        $viewData = [
            'sidebar' => Menu::mitraSidebar($id_mitra),
            'canCreate' => false,
            'canDelete' => false
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            $filter,
            $viewData,
            model: Kerjasama::class,
            customMethod: 'indexForMitra',
            customMethodParams: [$id_mitra]
        );
    }

    public function show(Request $request, $id_mitra, $id)
    {
        return redirect()->route('kerjasama.data-kerjasama.show', $id);
    }

    private function defineFilter(): array
    {
        return [
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
}
