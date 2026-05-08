<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Export\ExportPenilaianMatriks;
use Modules\SPMI\Helpers\Menu;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingPenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Services\PenilaianMatriksIKTManagementService;

class PenilaianMatriksIKTController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PenilaianMatriksIKTManagementService $service) {}

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
            ['field' => 'action', 'component' => 'penilaian_matriks_ikt', 'searchable' => false]
        ];

        $viewData['sidebar'] = Menu::masterSidebar('accreditation');

        $optPenilaianPanduan = PenilaianPanduan::options();
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
            $idJenisPenilaian = array_key_first($optJenisPenilaian);
            session(['filter_spmi_penilaian_matriks_jenis_penilaian' => $idJenisPenilaian]);
        }

        $filter = [
            'id_penilaian_panduan' => [
                'options' => $optPenilaianPanduan,
                'label' => 'Panduan Penilaian',
                'selected' => $idPenilaianPanduan
            ],
        ];

        $viewData['title'] = 'Matriks Penilaian Indikator Kinerja Tambahan';
        $viewData['subtitle'] = '';

        return WebController::index($this->service, $request, $header, $filter, $viewData);
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

        $penilaianPanduan = PenilaianPanduan::find(
            PenilaianMatriks::where('id', $id)->value('id_penilaian_panduan')
        );

        if ($penilaianPanduan->apakah_data_default) {
            unset($cards[17]);
            unset($cards[18]);
            $cards = array_values($cards);
        }

        // view data
        $viewData['sidebar'] = Menu::PenilaianMatriksIktSidebar($id);
        $viewData['page_conf'][] = ['custom_page' => ['component' => 'detail', 'data' => $this->service->customDetailPage($id)]];
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.accreditation.penilaian-matriks');
        $viewData['isDetailV2'] = true;
        $viewData['isFullwidth'] = true;

        $data = $this->service->show($id);
        $mappingPenilaian = MappingPenilaianMatriks::where('spmi.mapping_penilaian_matriks.id_penilaian_matriks', $id)
            ->join('core.unit_kerja as uk', 'uk.id', '=', 'spmi.mapping_penilaian_matriks.id_unit')
            ->join('spmi.audit_periode as ap', 'ap.id', '=', 'spmi.mapping_penilaian_matriks.id_audit_periode')
            ->where('uk.waktu_dihapus', null)
            ->where('ap.waktu_dihapus', null)
            ->select('spmi.mapping_penilaian_matriks.*')
            ->get();

        $program_studi = $mappingPenilaian->pluck('id_unit')->unique()->toArray();
        $data->program_studi = array_values($program_studi);
        $periode_ami = $mappingPenilaian->pluck('id_audit_periode')->unique()->toArray();
        $data->periode_ami = implode('::::', AuditPeriode::whereIn('id', $periode_ami)->pluck('tahun_audit')->toArray());

        $penilaian = PenilaianMatriksReferensi::select('id_butir_referensi')
            ->where('id_penilaian_matriks', $id)
            ->pluck('id_butir_referensi')
            ->toArray();

        if ($data->referensi_penilaian == PenilaianMatriks::REFERENCE_SELF_EVALUATION) {
            $ref = IndikatorEvaluasiDiri::whereIn('id', $penilaian)->pluck('nama_indikator_evaluasi_diri', 'id')->toArray();
        }
        if ($data->referensi_penilaian == PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT) {
            $ref = IndikatorLaporanKinerja::whereIn('id', $penilaian)->pluck('nama_indikator_laporan_kinerja', 'id')->toArray();
        }

        if (isset($ref) && !empty($ref)) {
            $data->referensi_penilaian_butir = implode('::::', $ref);
        }

        return WebController::show(
            service: $this->service,
            id: $id,
            cards: $cards,
            model: PenilaianMatriks::class,
            viewData: $viewData,
            data: $data
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
     * Download Error Report Excel
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function downloadErrorReport(Request $request)
    {
        $encodedFileName = $request->query('file');

        if (empty($encodedFileName)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $fileName = base64_decode($encodedFileName);
        $filePath = storage_path('app/public/temp/' . $fileName);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File error sudah tidak tersedia atau sudah dihapus.');
        }

        return response()->download($filePath, 'Error_Report_Import_' . date('YmdHis') . '.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
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
            ['field' => 'id_penilaian_panduan'],
            ['field' => 'id_parent'],
            ['field' => 'kategori_penilaian'],
            ['field' => 'nomor_penilaian'],
            ['field' => 'pertanyaan_penilaian', 'control' => 'textarea'],
            ['field' => 'id_akreditasi_standar'],
            ['field' => 'apakah_aktif', 'control' => 'radio', 'selected' => '1'],
            ['separator' => true, 'label' => 'Penilaian'],
            ['field' => 'apakah_parent_custom', 'control' => 'switch', 'label' => 'Apakah Parent'],
            ['field' => 'jenis_penilaian', 'selected' => PenilaianMatriks::TYPE_QUALITATIVE],
            ['field' => 'bobot_penilaian'],
            ['field' => 'referensi_penilaian'],
            ['field' => 'referensi_penilaian_butir'],
            //kurang field butir ...
            ['field' => 'deskripsi', 'control' => 'textarea'],
            ['field' => 'periode_ami', 'options' => $periods],
            ['field' => 'program_studi', 'options' => $units, 'label' => 'Unit Kerja'],
            ['field' => 'apakah_nilai_ditampilkan', 'control' => 'switch'],
            ['field' => 'butir_indikator_spme', 'control' => 'switch'],
            ['field' => 'butir_indikator_iku', 'control' => 'switch', 'label' => 'Hitung dalam Peringkat SPME / IKU']
        ];
    }
}
