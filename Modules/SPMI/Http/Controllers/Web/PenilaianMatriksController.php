<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Jobs\ProcessReorderMatrix;
use Modules\Core\Models\Shared\KlienConfig;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Services\PenilaianMatriksManagementService;

class PenilaianMatriksController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PenilaianMatriksManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'pertanyaan_penilaian', 'label' => 'Elemen, Dimensi & Indikator', 'component' => true],
            ['field' => 'kategori_penilaian', 'component' => true],
            ['field' => 'jenis_penilaian', 'component' => true],
            ['field' => 'referensi_penilaian', 'component' => true],
            ['field' => 'bobot_penilaian'],
            ['field' => 'apakah_aktif_matriks', 'component' => true],
            ['field' => 'apakah_data_default', 'label' => 'Kategori', 'component' => 'apakah_iku_ikt', 'searchable' => false],
            ['field' => 'action', 'component' => 'penilaian_matriks', 'searchable' => false]
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optPenilaianPanduan = PenilaianPanduan::optionsDefault();
        $optJenisPenilaian = ['-' => 'Semua Jenis Penilaian'] + PenilaianMatriks::TYPES;

        $idPenilaianPanduan = $request->filter['id_penilaian_panduan'] ?? session('filter_spmi_penilaian_matriks_id_penilaian_panduan') ?? array_key_first($optPenilaianPanduan);
        $idJenisPenilaian = $request->filter['jenis_penilaian'] ?? session('filter_spmi_penilaian_matriks_jenis_penilaian') ?? '-';

        if (!empty($idPenilaianPanduan)) {
            session(['filter_spmi_penilaian_matriks_id_penilaian_panduan' => $idPenilaianPanduan]);
        }

        if (!empty($idJenisPenilaian)) {
            session(['filter_spmi_penilaian_matriks_jenis_penilaian' => $idJenisPenilaian]);
        }

        if (!isset($optPenilaianPanduan[$idPenilaianPanduan])) {
            $idPenilaianPanduan = array_key_first($optPenilaianPanduan);
            session(['filter_spmi_penilaian_matriks_id_penilaian_panduan' => $idPenilaianPanduan]);
        }

        if (!isset($optJenisPenilaian[$idJenisPenilaian])) {
            $idJenisPenilaian = '-';
            session(['filter_spmi_penilaian_matriks_jenis_penilaian' => $idJenisPenilaian]);
        }

        $filter = [
            'id_penilaian_panduan' => [
                'options' => $optPenilaianPanduan,
                'label' => 'Panduan Penilaian',
                'selected' => $idPenilaianPanduan
            ],
            'jenis_penilaian' => [
                'options' => $optJenisPenilaian,
                'label' => 'Jenis Penilaian',
                'selected' => $idJenisPenilaian,
            ],
        ];


        $viewData['title'] = 'Matriks Penilaian Indikator Kinerja Utama';
        $viewData['subtitle'] = 'Indikator Kinerja Utama';

        $request->merge([
            'permission' => [
                'post' => false,
                'put' => false,
                'delete' => false,
                'custom' => false,
                'get' => true,
            ]
        ]);

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    public function reorderForm($idPenilaianPanduan)
    {
        if (!auth()->user()->apakah_role_internal) {
            abort(404);
        }

        $penilaianPanduan = PenilaianPanduan::findOrFail($idPenilaianPanduan);

        $redirect = $penilaianPanduan->apakah_data_default
            ? route('spmi.penilaian-matriks.index')
            : route('spmi.penilaian-matriks-ikt.index');

        try {
            PenilaianMatriks::resyncTreeStructure($idPenilaianPanduan);
        } catch (\Exception $e) {
            return redirect($redirect)->with('error', 'Gagal menyusun ulang matriks penilaian: ' . $e->getMessage());
        }

        return redirect($redirect)->with('success', 'Proses pengurutan matriks penilaian telah dimulai. Silakan tunggu beberapa saat dan muat ulang halaman untuk melihat hasilnya.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        WebController::validate($id);

        $cards = $this->defineFormFields();

        // view data
        $viewData['sidebar'] = Menu::PenilaianMatriksSidebar($id);
        $viewData['page_conf'][] = ['custom_page' => ['component' => 'detail', 'data' => $this->service->customDetailPage($id)]];
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-matriks');
        $viewData['isDetailV2'] = true;
        $viewData['isFullwidth'] = true;

        return WebController::show($this->service, $id, $cards, PenilaianMatriks::class, $viewData);
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
            ['field' => 'id_penilaian_panduan'],
            ['field' => 'id_parent'],
            ['field' => 'kategori_penilaian'],
            ['field' => 'nomor_penilaian'],
            ['field' => 'pertanyaan_penilaian', 'control' => 'textarea'],
            ['field' => 'id_akreditasi_standar'],
            ['field' => 'apakah_aktif', 'control' => 'radio', 'selected' => '1'],
            ['separator' => true, 'label' => 'Penilaian'],
            ['field' => 'jenis_penilaian', 'selected' => PenilaianMatriks::TYPE_QUALITATIVE],
            ['field' => 'bobot_penilaian'],
            ['field' => 'referensi_penilaian'],
            //kurang field butir ...
            ['field' => 'deskripsi', 'control' => 'textarea'],
            ['field' => 'apakah_nilai_ditampilkan', 'control' => 'switch'],
            ['field' => 'butir_indikator_spme', 'control' => 'switch']
        ];
    }
}
