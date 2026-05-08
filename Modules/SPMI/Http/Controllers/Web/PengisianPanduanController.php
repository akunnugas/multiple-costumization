<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Services\PengisianPanduanManagementService;

class PengisianPanduanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PengisianPanduanManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        // FIXME: Belum ada show image [DMS]
        $header = [
			['field' => 'nama_pengisian_panduan'],
			['field' => 'nama_singkat'],
			['field' => 'tipe_edisi', 'options' => AkreditasiBuku::TYPE, 'searchable' => false],
            ['field' => 'nama_lembaga_akreditasi'],
            ['field' => 'apakah_aktif', 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => "pengisian_panduan_action", 'searchable' => false],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.pengisian-panduan');
        return WebController::create($this->defineFormFields(), PengisianPanduan::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), PengisianPanduan::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.pengisian-panduan');
        $viewData['isDetailV2'] = true;

        $pengisianPanduan = PengisianPanduan::findOrFail($id);
        foreach ($cards as $index => $card) {
            if ($card['field'] == 'id_pengisian_panduan') {
                if ($pengisianPanduan->tipe_edisi == AkreditasiBuku::SELF_EVALUATION) {
                    $namaPanduan = PengisianPanduan::where('id_pengisian_panduan', $pengisianPanduan->id)->first();
                    $cards[$index]['label'] = 'Panduan Laporan Kinerja';
                    $cards[$index]['text'] = $namaPanduan ? $namaPanduan->nama_singkat : '-';
                } elseif ($pengisianPanduan->tipe_edisi == AkreditasiBuku::PERFORMANCE_REPORT) {
                    $namaPanduan = PengisianPanduan::where('id', $pengisianPanduan->id_pengisian_panduan)->first();
                    $cards[$index]['label'] = 'Panduan Evaluasi Diri';
                    $cards[$index]['text'] = $namaPanduan ? $namaPanduan->nama_singkat : '-';
                }
            }
        }

        if ($pengisianPanduan->apakah_data_default) {
            $request->merge(['permission' => array_merge($request->permission, ['delete' => false, 'post' => false, 'put' => false])]);
        }

        return WebController::show($this->service, $id, $cards, PengisianPanduan::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.pengisian-panduan');
        return WebController::edit($this->service, $id, $this->defineFormFields(), PengisianPanduan::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), PengisianPanduan::class);
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
            ['field' => 'kode_pengisian_panduan'],
			['field' => 'nama_pengisian_panduan'],
			['field' => 'nama_singkat'],
            ['field' => 'id_lembaga_akreditasi'],
            ['field' => 'tipe_edisi'],
            ['field' => 'id_pengisian_panduan'],
            ['field' => 'id_jenjang_pendidikan', 'label' => 'Jenjang Pendidikan'],
			['field' => 'deskripsi', 'control' => 'textarea'],
			['field' => 'tanggal_edisi', 'type' => 'date'],
			['field' => 'tanggal_efektif', 'type' => 'date'],
			['field' => 'tanggal_kadaluwarsa', 'type' => 'date'],
            ['field' => 'apakah_aktif', 'control' => 'switch', 'label' => 'Status Aktif Panduan', 'helper' => 'Aktifkan jika panduan ini digunakan dalam proses SPMI saat ini.'],
            ['field' => 'id_dokumen'],
            ['field' => 'id_tipe', 'type' => 'hidden', 'selected' => request()->type_id],
        ];
    }
}
