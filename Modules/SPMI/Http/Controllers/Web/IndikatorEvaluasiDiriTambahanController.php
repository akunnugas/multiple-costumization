<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\MappingLED;
use Modules\SPMI\Services\IndikatorEvaluasiDiriManagementService;

class IndikatorEvaluasiDiriTambahanController extends Controller
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
            ['field' => 'nomor_indikator', 'label' => 'Nomor Komponen', 'order' => 'desc'],
            ['field' => 'nama_indikator_evaluasi_diri', 'label' => 'Nama Komponen Evaluasi Diri', 'is_tree_view' => true],
            ['field' => 'apakah_parent', 'label' => 'Butir Utama? (Parent)', 'component' => 'apakah_parent', 'searchable' => false],
            ['field' => 'action', 'component' => 'indikator_evaluasi_diri_tambahan', 'searchable' => false]
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optPengisianPanduan = PengisianPanduan::getListSelfEvaluation();
        $idPengisianPanduan = $request->filter['id_pengisian_panduan'] ?? session('filter_spmi_led_ikt_id_pengisian_panduan') ?? array_key_first($optPengisianPanduan);

        if (!empty($idPengisianPanduan)) {
            session(['filter_spmi_led_ikt_id_pengisian_panduan' => $idPengisianPanduan]);
        }

        if (!isset($optPengisianPanduan[$idPengisianPanduan])) {
            $idPengisianPanduan = array_key_first($optPengisianPanduan);
            session(['filter_spmi_led_ikt_id_pengisian_panduan' => $idPengisianPanduan]);
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
                'selected' => 'false',
                'hideView' => true
            ],
        ];

        $viewData['title'] = 'Komponen Evaluasi Diri (ED) Tambahan';
        $viewData['subtitle'] = '';

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-led-tambahan');
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

        $viewData['sidebar'] = Menu::indicatorLEDTambahan($id);
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-led-tambahan');
        $viewData['isDetailV2'] = true;

        $data = $this->service->show($id);
        $mappingLED = MappingLED::where('id_indikator_evaluasi_diri', $id)->get();
        $program_studi = $mappingLED->pluck('id_unit')->unique()->toArray();
        $data->program_studi = array_values($program_studi);
        $periode_ami = $mappingLED->pluck('id_audit_periode')->unique()->toArray();
        $data->periode_ami = array_values($periode_ami);

        return WebController::show(
            service: $this->service,
            id: $id,
            cards: $cards,
            model: IndikatorEvaluasiDiri::class,
            viewData:$viewData,
            data: $data,
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.indikator-led-tambahan');
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
        if (MappingLED::where('id_indikator_evaluasi_diri', $id)->exists()) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Tidak dapat menghapus data yang sudah dimappingkan.'
                );
        }
        return WebController::destroy($this->service, $id);
    }

    /**
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
    public function destroySome(Request $request)
    {
        $butirIds = $request->group;
        if (MappingLED::whereIn('id_indikator_evaluasi_diri', $butirIds)->exists()) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Beberapa data komponen evaluasi diri tambahan tidak dapat dihapus. Karena sedang digunakan pada pemetaan LED.'
                );
        }
        return WebController::destroySome($this->service, $request);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        $listPR = PengisianPanduan::getListSelfEvaluation();
        $units = UnitKerja::select('id', 'nama_unit', 'id_jenjang_pendidikan')
            ->with('jenjang')
            ->whereIn('jenis_unit', [UnitKerja::UNIT_NON_PRODI, UnitKerja::STUDY_PROGRAM])
            ->get();
        $units = $units->map(function ($item) {
            $jenjang = '';
            if (!empty($item->jenjang)) {
                $jenjang = $item->jenjang->kode_jenjang . ' - ';
            }

            return [
                'id' => $item->id,
                'nama_unit' => $jenjang . $item->nama_unit
            ];
        })->toArray();
        $units = array_column($units, 'nama_unit', 'id');

        $periods = AuditPeriode::select('id', 'tahun_audit')
            ->orderBy('tahun_audit', 'desc')
            ->pluck('tahun_audit', 'id')
            ->toArray();

        return [
            ['field' => 'id_pengisian_panduan', 'options' => $listPR, 'label' => 'Panduan Pengisian'],
            ['field' => 'id_parent', 'options' => IndikatorEvaluasiDiri::class, 'label' => 'Butir Parent'],
            ['field' => 'nomor_indikator'],
            ['field' => 'nama_indikator_evaluasi_diri'],
            ['field' => 'deskripsi', 'control' => 'textarea'],
            // ['field' => 'apakah_komentar', 'control' => 'switch'],
            // ['field' => 'apakah_key_point', 'control' => 'switch'],
            ['field' => 'apakah_parent', 'control' => 'switch'],
            ['field' => 'apakah_aktif', 'control' => 'switch'],
            ['field' => 'periode_ami', 'options' => $periods],
            ['field' => 'program_studi', 'options' => $units],
        ];
    }
}
