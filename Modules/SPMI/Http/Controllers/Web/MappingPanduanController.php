<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\MappingPanduanManagementService;

class MappingPanduanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private MappingPanduanManagementService $service)
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
            ['field' => 'kode_penilaian_panduan', 'label' => 'Kode Panduan'],
            ['field' => 'nama_penilaian_panduan', 'label' => 'Panduan Penilaian'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optPengisianPanduan = PengisianPanduan::getListIndicatorPerformanceReport();

        $idPengisianPanduan = $request->get('mp_id_pengisian_panduan') ?? session('filter_mapping_panduan') ?? array_keys($optPengisianPanduan)[0];

        if (!empty($idPengisianPanduan)) {
            session(['filter_mapping_panduan' => $idPengisianPanduan]);
        }

        if (!isset($optPengisianPanduan[$idPengisianPanduan])) {
            $idPengisianPanduan = array_key_first($optPengisianPanduan);
            session(['filter_mapping_panduan' => $idPengisianPanduan]);
        }

        $filter = [
            'mp.id_pengisian_panduan' => [
                'options' => $optPengisianPanduan,
                'label' => 'Panduan Pengisian',
                'selected' => $idPengisianPanduan
            ],
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData, isReference: true)
            ->withPermission($request->permission);
    }

    /**
     * store mapping.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $data['id_pengisian_panduan'] = $request->input('mp_id_pengisian_panduan') ?? session('filter_mapping_panduan');

        foreach ($request->data as $item) {
            if (isset($item['selected']) && !empty($item['selected'])) {
                $data['mapping'][] = $item;
            }
        }

        $status = $this->service->store($data);

        return to_route('spmi.mapping-panduan.index', ['id_pengisian_panduan' => $data['id_pengisian_panduan']])
            ->with(($status == 1 ? 'success' : 'error'), ($status == 1 ? 'Mapping berhasil disimpan' : $status));
    }
}
