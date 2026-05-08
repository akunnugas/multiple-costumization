<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\UI;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PengisianIndikator;
use Modules\SPMI\Models\DataPengisianLK;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\MappingLK;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\AuditPeriodeManagementService;
use Modules\SPMI\Services\PengisianIndikatorManagementService;
use Modules\SPMI\Services\IndikatorLaporanKinerjaManagementService;

class PengisianIndikatorController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PengisianIndikatorManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'periode_audit', 'searchable' => false],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'nama_unit', 'label' => 'Unit Kerja / Program Studi', 'order' => 'asc'],
            ['field' => 'nama_lembaga_akreditasi'],
            ['field' => 'nama_pengisian_panduan', 'label' => 'Panduan Penilaian yang Digunakan'],
            ['field' => 'status_pengisian_indikator', 'label' => 'Progres Pengisian Indikator', 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => 'pengisian_indikator', 'searchable' => false]
        ];


        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_pengisian_indikator_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_pengisian_indikator_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_pengisian_indikator_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_pengisian_indikator_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_pengisian_indikator_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_pengisian_indikator_unit_kerja' => $idUnitKerja]);
        }

        $filter = [
            'id_audit_periode' => [
                'options' => $optAuditPeriode,
                'label' => 'Periode AMI',
                'selected' => $idAuditPeriode
            ],
            'id_jenjang_pendidikan' => [
                'options' => $optJenjangPendidikan,
                'label' => 'Jenjang Pendidikan',
                'selected' => $idJenjangPendidikan,
                'is_empty' => true
            ],
            'id_unit_kerja' => [
                'options' => $optUnitKerja,
                'label' => 'Unit Kerja',
                'selected' => $idUnitKerja,
                'is_empty' => true
            ],
        ];

        $viewData = [
            'showNumber' => true,
            'emptyState' => [
                'title' => 'Belum Ada Data Pengisian Laporan Kinerja',
                'subtitle' => 'Silakan tambahkan data pengisian laporan kinerja dengan cara memetakan unit dengan panduan akreditasi di <a class="a-link" href="' . route('spmi.unit-kerja.index') . '">halaman unit</a>'
            ]
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $invalidParam = !WebRequest::validateId($request->id_unit) || !WebRequest::validateId($request->periode_audit || !WebRequest::validateId($request->id_pengisian_panduan));

        if (!($request->has('id_unit') && $request->has('periode_audit') || $request->has('id_pengisian_panduan')) || $invalidParam) {
            abort(404);
        }

        $yearData = AuditPeriode::findByYear($request->periode_audit);
        $fillingIndicator = PengisianIndikator::findByStudyProgramIdAndType($yearData->id, $request->id_pengisian_panduan, $request->id_unit, AkreditasiBuku::PERFORMANCE_REPORT, $request->id_jadwal_audit);

        // if filling indicator has found or audit year has found
        if ($fillingIndicator) {
            return redirect()->route('spmi.pengisian-indikator.edit', [$fillingIndicator->id]);
        }

        // declare url for create
        $URLCreate = Page::createURL() . "?id_unit={$request->id_unit}&periode_audit={$request->periode_audit}&id_pengisian_panduan={$request->id_pengisian_panduan}&id_jadwal_audit={$request->id_jadwal_audit}";

        // get information and raw data for create
        $data = $this->service->showInformationByStudyProgram((int) $request->id_unit, $yearData?->toArray(), $request->id_pengisian_panduan, $request->id_jadwal_audit);

        if (Error::isError($data)) {
            if ($data->code == 404) {
                abort(404);
            }

            return $data->redirectBack();
        }

        // declare information and raw data
        [$information, $raw, $apakahTanggalPengisianValid, $apakahPengisianBelumDimulai] = $data;

        // create tree view
        $indicatorTree = (new IndikatorLaporanKinerjaManagementService)->showAllIndicatorsByPengisianPanduan(
            $request->id_pengisian_panduan,
            $raw['id_audit_periode'],
            $raw['id_unit'],
            true,
            $URLCreate .
                "&id_indikator_laporan_kinerja="
        );
        // get child active
        if (!empty($request->id_indikator_laporan_kinerja)) {
            $childActive = IndikatorLaporanKinerja::find($request->id_indikator_laporan_kinerja);
            if (!$childActive) {
                return abort(404);
            }
            $childActive = $childActive->toArray(['info_left', 'info_right']);
        } else {
            // redirect this page with add new param
            $firstIndicator = IndikatorLaporanKinerja::where('id_pengisian_panduan', $raw['id_pengisian_panduan'])->where('apakah_parent', false)->orderBy('nomor_indikator', 'asc')->first();
            return redirect()->to($URLCreate . "&id_indikator_laporan_kinerja=" . $firstIndicator->id);
        }

        $htmlTree = UI::createTreeView($indicatorTree, $childActive ?? null);

        $raw['id_indikator_laporan_kinerja'] = $request->id_indikator_laporan_kinerja;
        $yearData = AuditPeriode::find($raw['id_audit_periode']);

        $isFoundMappingLK = MappingLK::where('id_indikator_laporan_kinerja', $request->id_indikator_laporan_kinerja)
            ->where('id_unit', $raw['id_unit'])
            ->where('id_audit_periode', $raw['id_audit_periode'])
            ->exists();

        // set global unit
        Config::set('spmi.filling_indicator', $raw['id_unit']);

        // get pengisian panduan
        $pengisianPanduan = PengisianPanduan::find($raw['id_pengisian_panduan']);

        // init class
        $classPath = ('Modules\SPMI\Services\Pengisian\\' . str_replace('.', '', $pengisianPanduan->kode_pengisian_panduan) . "ManagementService");
        if ($pengisianPanduan->apakah_data_default && !$pengisianPanduan->apakah_iku_kualitatif && class_exists($classPath)) {
            // get function
            $class = new $classPath($yearData->tahun_audit);
            $function = 'get' . str_replace('.', '', $childActive['nomor_indikator']);
            $function = str_replace('-', '_', $function);

            // get data
            if ($childActive['apakah_data_default']) {
                list($table, $table_row, $table_footer, $table_data, $isHasNumber, $isHasAction, $updatedIndicator) = $class->$function($raw);

                list($table_column, $table_input, $table_disabled) = $this->service->convertTable($table, $isHasNumber, $isHasAction);
            }
        }

        // check for module HR is active
        $isActiveHR = session('is_active_hr');
        if (is_null($isActiveHR)) {
            $isActiveHR = DB::connection('siakadv1')->select("select isaktif from gate.sc_modul where idmodul = 'hr'");
            $isActiveHR = json_decode(json_encode($isActiveHR), true)[0]['isaktif'] ?? false;
            session(['is_active_hr' => $isActiveHR]);
        }
        $isTarikDataSDM = $childActive['sumber_data'] == IndikatorLaporanKinerja::DATA_SDM;

        if ($request->has('id_indikator_laporan_kinerja')) {
            $indicatorLaporanKinerja = IndikatorLaporanKinerja::find($request->id_indikator_laporan_kinerja);
            $data[] = $indicatorLaporanKinerja->toArray();
            $index = count($data) - 1;
            $data[$index]['previous_year_audit'] = [];
            $data[$index]['audit_period_label'] = '';
            list($success, $prevYearAudit) = $this->service->getPreviousSelectOptions(
                idAuditPeriode: $raw['id_audit_periode'],
                idUnit: $raw['id_unit'],
                idPengisianPanduan: $raw['id_pengisian_panduan'],
            );
            $auditPeriod = (new AuditPeriodeManagementService())->show((int) $raw['id_audit_periode']);
            if ($auditPeriod) {
                $data[$index]['audit_period_label'] = $auditPeriod->tahun_audit;
            }
            if ($success && $prevYearAudit) {
                $prevYearAuditOptions = [];
                foreach ($prevYearAudit as $item) {
                    $key = $item->id . '|' . $item->id_jadwal_audit;
                    $label = $item->tahun_audit;
                    if (!empty($item->nama_jadwal_audit)) {
                        $label .= ' - ' . $item->nama_jadwal_audit;
                    }
                    $prevYearAuditOptions[$key] = $label;
                }
                $data[$index]['previous_year_audit'] = $prevYearAuditOptions;
            }
        }

        $payload = [];
        if ($pengisianPanduan->apakah_data_default && !$pengisianPanduan->apakah_iku_kualitatif && $childActive['apakah_data_default']) {
            $payload = [
                'table_key' => $raw,
                'table_column' => $table_column,
                'table_input' => $table_input,
                'table_row' => $table_row,
                'table_footer' => $table_footer,
                'table_data' => $table_data,
                'table_disabled' => $table_disabled,
                'indicator' => ($updatedIndicator + $childActive)
            ];
        } else {
            $payload = [
                'table_key' => $raw,
                'table_column' => [],
                'table_input' => [],
                'table_row' => [],
                'table_footer' => [],
                'table_data' => [],
                'table_disabled' => [],
                'indicator' => $childActive
            ];
        }

        return WebController::buildView('create')
            ->withData(array_merge([
                'sidebar' => $htmlTree,
                'information' => $information,
                'link_proposing_team' => $URLCreate,
                'data' => $data,
                'raw' => $raw,
                'apakah_panduan_default' => $pengisianPanduan->apakah_data_default,
                'apakah_iku_kualitatif' => $pengisianPanduan->apakah_iku_kualitatif,
                'apakah_sudah_mapping' => ($isFoundMappingLK ?? false),
                'apakah_tanggal_pengisian_valid' => $apakahTanggalPengisianValid,
                'apakah_pengisian_belum_dimulai' => $apakahPengisianBelumDimulai,
                'apakah_bisa_aksi' => $request->permission['post'] ?? false,
                'apakah_hr_aktif' => $isActiveHR,
                'apakah_tarik_data_hr' => $isTarikDataSDM
            ], $payload));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $successMessage = 'Berhasil menyimpan data';
        $studyProgramId = $request->self['id_unit'];
        $periodeAuditId = $request->self['id_audit_periode'];
        $idPengisianPanduan = $request->self['id_pengisian_panduan'];
        $invalidParam = !WebRequest::validateId($studyProgramId);

        if (empty($studyProgramId) || $invalidParam) {
            return redirect()->route('spmi.pengisian-indikator.index')->withError('Data tidak ditemukan');
        }

        $fillingIndicator = PengisianIndikator::findByStudyProgramIdAndType($periodeAuditId, $idPengisianPanduan, $studyProgramId, AkreditasiBuku::PERFORMANCE_REPORT, $request->self['id_jadwal_audit'] ?? null);
        if ($fillingIndicator) {
            return $this->update($request, $fillingIndicator->id);
        }

        $data = [];
        if (!empty($request->act) && $request->act == 'fillinggetdata') {
            $data['data'] = $this->fillingGetData($request->self);
        }
        $data = array_merge($request->all(), $data);

        $return = $this->service->store($data);

        if ($request->act === 'upload-dokumen-pendukung') {
            $successMessage = 'Berhasil mengupload dokumen pendukung';
        }

        if (Error::isError($return)) {
            return $return->redirectBack()->withError($return->message);
        } else {
            // redirect on spesific indicator performance report
            $id = $return->id;
            $url = Page::editURL($id);

            if (!empty($request->self['id_indikator_laporan_kinerja'])) {
                $url .= "?id_indikator_laporan_kinerja={$request->self['id_indikator_laporan_kinerja']}";
            }

            return redirect()->to($url)->withSuccess($successMessage);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, PengisianIndikator::class);
    }

    public function deleteDokumen($id, Request $request, $idDokumen)
    {
        $return = $this->service->deleteDokumenPendukung($id, $idDokumen);

        if (Error::isError($return)) {
            return $return->redirectBack()->withError($return->message);
        } else {
            $url = route('spmi.pengisian-indikator.edit', $id);

            if (!empty($return->id_indikator_laporan_kinerja)) {
                $url .= "?id_indikator_laporan_kinerja={$return->id_indikator_laporan_kinerja}";
            }

            return redirect()->to($url)->withSuccess('Berhasil menghapus dokumen pendukung');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id, Request $request)
    {
        $invalidParam = !WebRequest::validateId($id);

        if (empty($id) || $invalidParam) {
            abort(404);
        }

        // get information and raw data
        $data = $this->service->showInformation((int) $id);

        if (Error::isError($data)) {
            if ($data->code == 404) {
                abort(404);
            }
            return $data->redirectBack();
        }

        // declare information and raw data
        [$information, $raw, $apakahTanggalPengisianValid, $apakahPengisianBelumDimulai] = $data;
        $raw['id_indikator_laporan_kinerja'] = $request->id_indikator_laporan_kinerja;
        $yearData = AuditPeriode::find($raw['id_audit_periode']);

        $isFoundMappingLK = MappingLK::where('id_indikator_laporan_kinerja', $request->id_indikator_laporan_kinerja)
            ->where('id_unit', $raw['id_unit'])
            ->where('id_audit_periode', $raw['id_audit_periode'])
            ->exists();

        Config::set('spmi.filling_indicator', $raw['id_unit']);

        // declare url for edit
        $editURL = Page::editURL($id);
        // create tree view
        $indicatorTree = (new IndikatorLaporanKinerjaManagementService)->showAllIndicatorsByPengisianPanduan(
            $raw['id_pengisian_panduan'],
            $raw['id_audit_periode'],
            $raw['id_unit'],
            true,
            $editURL .
                "?id_indikator_laporan_kinerja="
        );
        // get child active
        if (!empty($request->id_indikator_laporan_kinerja)) {
            $childActive = IndikatorLaporanKinerja::find($request->id_indikator_laporan_kinerja);
            if (!$childActive) {
                return abort(404);
            }
            $childActive = $childActive->toArray(['info_left', 'info_right']);
        } else {
            // redirect this page with add new param
            $firstIndicator = IndikatorLaporanKinerja::where('id_pengisian_panduan', $raw['id_pengisian_panduan'])->where('apakah_parent', false)->orderBy('nomor_indikator', 'asc')->first();
            return redirect()->to($editURL . "?id_indikator_laporan_kinerja=" . $firstIndicator->id);
        }

        $htmlTree = UI::createTreeView($indicatorTree, $childActive ?? null);

        // get pengisian panduan
        $pengisianPanduan = PengisianPanduan::find($raw['id_pengisian_panduan']);

        // init class
        $classPath = ('Modules\SPMI\Services\Pengisian\\' . str_replace('.', '', $pengisianPanduan->kode_pengisian_panduan . "ManagementService"));
        if ($pengisianPanduan->apakah_data_default && !$pengisianPanduan->apakah_iku_kualitatif && class_exists($classPath)) {
            // get function
            $class = new $classPath($yearData->tahun_audit);
            $function = 'get' . str_replace('.', '', $childActive['nomor_indikator']);
            $function = str_replace('-', '_', $function);

            // get data
            if ($childActive['apakah_data_default']) {
                list($table, $table_row, $table_footer, $table_data, $isHasNumber, $isHasAction, $updatedIndicator) = $class->$function($raw, $id);

                list($table_column, $table_input, $table_disabled) = $this->service->convertTable($table, $isHasNumber, $isHasAction);
            }
        }

        // get dokumen pendukung
        $dokumenPendukung = $this->service->getDokumenPendukung($id, $request->id_indikator_laporan_kinerja);

        // check for module HR is active
        $isActiveHR = session('is_active_hr');
        if (is_null($isActiveHR)) {
            $isActiveHR = DB::connection('siakadv1')->select("select isaktif from gate.sc_modul where idmodul = 'hr'");
            $isActiveHR = json_decode(json_encode($isActiveHR), true)[0]['isaktif'] ?? false;
            session(['is_active_hr' => $isActiveHR]);
        }
        $isTarikDataSDM = $childActive['sumber_data'] == IndikatorLaporanKinerja::DATA_SDM;

        if ($request->has('id_indikator_laporan_kinerja')) {
            $indicatorLaporanKinerja = IndikatorLaporanKinerja::find($request->id_indikator_laporan_kinerja);
            $data[] = $indicatorLaporanKinerja->toArray();
            $index = count($data) - 1;
            $data[$index]['previous_year_audit'] = [];
            $data[$index]['audit_period_label'] = '';
            list($success, $prevYearAudit) = $this->service->getPreviousSelectOptions(
                idAuditPeriode: $raw['id_audit_periode'],
                idUnit: $raw['id_unit'],
                idPengisianPanduan: $raw['id_pengisian_panduan'],
            );
            $auditPeriod = (new AuditPeriodeManagementService())->show((int) $raw['id_audit_periode']);
            if ($auditPeriod) {
                $data[$index]['audit_period_label'] = $auditPeriod->tahun_audit;
            }
            if ($success && $prevYearAudit) {
                $prevYearAuditOptions = [];
                foreach ($prevYearAudit as $item) {
                    $key = $item->id . '|' . $item->id_jadwal_audit;
                    $label = $item->tahun_audit;
                    if (!empty($item->nama_jadwal_audit)) {
                        $label .= ' - ' . $item->nama_jadwal_audit;
                    }
                    $prevYearAuditOptions[$key] = $label;
                }
                $data[$index]['previous_year_audit'] = $prevYearAuditOptions;
            }
        }

        $payload = [];
        if ($pengisianPanduan->apakah_data_default && !$pengisianPanduan->apakah_iku_kualitatif && $childActive['apakah_data_default']) {
            $payload = [
                'table_key' => $raw,
                'table_column' => $table_column,
                'table_input' => $table_input,
                'table_row' => $table_row,
                'table_footer' => $table_footer,
                'table_data' => $table_data,
                'table_disabled' => $table_disabled,
                'indicator' => ($updatedIndicator + $childActive)
            ];
        } else {
            $teksPengisian = DataPengisianLK::where('id_pengisian_indikator', $id)
                ->where('id_indikator_laporan_kinerja', $request->id_indikator_laporan_kinerja)
                ->value('teks_pengisian');
            $payload = [
                'table_key' => $raw,
                'table_column' => [],
                'table_input' => [],
                'table_row' => [],
                'table_footer' => [],
                'table_data' => [],
                'table_disabled' => [],
                'indicator' => $childActive,
                'teks_pengisian' => $teksPengisian
            ];
        }

        return WebController::buildView('create')
            ->withData(array_merge([
                'sidebar' => $htmlTree,
                'information' => $information,
                'link_proposing_team' => $editURL,
                'data' => $data,
                'raw' => $raw,
                'apakah_panduan_default' => $pengisianPanduan->apakah_data_default,
                'apakah_sudah_mapping' => ($isFoundMappingLK ?? false),
                'apakah_iku_kualitatif' => $pengisianPanduan->apakah_iku_kualitatif,
                'apakah_tanggal_pengisian_valid' => $apakahTanggalPengisianValid,
                'apakah_pengisian_belum_dimulai' => $apakahPengisianBelumDimulai,
                'apakah_bisa_aksi' => $request->permission['post'] ?? false,
                'dokumen_pendukung' => $dokumenPendukung,
                'apakah_hr_aktif' => $isActiveHR,
                'apakah_tarik_data_hr' => $isTarikDataSDM
            ], $payload))
            ->withResourceId($id);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $successMessage = 'Berhasil menyimpan data';
        $invalidParam = !WebRequest::validateId($id);
        $data = [];

        if (!empty($request->act) && $request->act == 'fillinggetdata') {
            $data['data'] = $this->fillingGetData($request->self);

            if (empty($data['data'])) {
                return redirect()->back()->withError('Data tidak tersedia');
            }
        }

        if (empty($id) || $invalidParam) {
            return redirect()->route('spmi.pengisian-indikator.index')->withError('Data tidak ditemukan');
        }

        $data = array_merge($request->all(), $data);
        $return = $this->service->update($data, $id);

        if ($request->act === 'upload-dokumen-pendukung') {
            $successMessage = 'Berhasil mengupload dokumen pendukung';
        }

        if (Error::isError($return)) {
            return $return->redirectBack();
        } else {
            // redirect on spesific indicator performance report
            $url = Page::editURL($id);

            if (!empty($request->self['id_indikator_laporan_kinerja'])) {
                $url .= "?id_indikator_laporan_kinerja={$request->self['id_indikator_laporan_kinerja']}";
            }

            return redirect()->to($url)->withSuccess($successMessage);
        }
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
        return [];
    }

    /**
     * Get Filling Data from SIAKAD V1
     */
    public function fillingGetData(array $raw)
    {
        // get pengisian panduan
        $pengisianPanduan = PengisianPanduan::find($raw['id_pengisian_panduan']);

        // get indicator performance report
        $indicatorPerformanceReport = IndikatorLaporanKinerja::find($raw['id_indikator_laporan_kinerja']);
        // year
        $auditPeriod = AuditPeriode::find($raw['id_audit_periode']);
        $getData = [];
        $param = [];
        $param['tahun_audit'] = $auditPeriod->tahun_audit;
        $param['id_unit'] = $raw['id_unit'];

        // get function name by nomor indikator
        $function = 'get' . str_replace('.', '', $indicatorPerformanceReport['nomor_indikator']);
        $function = str_replace('-', '_', $function);

        // get filling get data class by kode pengisian panduan
        $classPath = ('Modules\SPMI\Data\FillingGetData\\' . str_replace('.', '', $pengisianPanduan->kode_pengisian_panduan) . "Kriteria");;
        $fillingGetData = new $classPath;

        // special case for IAPS9
        if ($pengisianPanduan->kode_pengisian_panduan == 'IAPS9') {
            switch ($indicatorPerformanceReport['nomor_indikator']) {
                case '5a':
                    $param['id_kurikulum'] = request()->get('key');
                    break;
                case '8c.2':
                    $function = 'get8c1';
                    $param['maximal'] = 4;
                    $param['minimal'] = 7;
                    break;
            }
        } else if ($pengisianPanduan->kode_pengisian_panduan == 'IAPS5.1') {
            $param['id_unit'] = UnitKerja::find($raw['id_unit'])->ref_key_siakad;
        }

        // call function
        $getData = $fillingGetData->$function($param);

        return $getData;
    }

    /**
     * show report
     */
    public function showReport()
    {
        $raw = $_POST['self'];
        $data = $this->service->showReport($raw['id_pengisian_panduan']);
        $auditPeriod = AuditPeriode::find($raw['id_audit_periode']);
        $studyProgram = UnitKerja::find($raw['id_unit']);

        $records =  PengisianIndikator::getRecordByFilter($raw['id_audit_periode'], $raw['accreditation_agency_id'], $raw['id_pengisian_panduan'], $raw['id_unit'], idJadwalAudit: $raw['id_jadwal_audit'] ?? null);

        $PengisianPanduan = PengisianPanduan::find($raw['id_pengisian_panduan']);
        $title = $PengisianPanduan->name;

        $information = [];
        $information['periode_audit'] = $auditPeriod->year;
        $information['study_program'] =  $studyProgram->degree->code . ' ' . $studyProgram->name;

        Config::set('spmi.filling_indicator', $raw['id_unit']);

        return view('spmi::pages.pengisian-indikator.report', compact('data', 'information', 'records', 'title'));
    }

    public function deleteAll(string $id, string $idIndikatorLaporanKinerja)
    {
        DataPengisianLK::where('id_pengisian_indikator', $id)
            ->where('id_indikator_laporan_kinerja', $idIndikatorLaporanKinerja)
            ->delete();

        return redirect()->back()->withSuccess('Berhasil menghapus semua data');
    }

    public function copyFromOtherPeriod(Request $request)
    {
        $rules = [
            'id_audit_periode' => 'required|integer',
            'id_unit' => 'required|integer',
            'id_pengisian_panduan' => 'required|integer',
            'previous_source' => 'required|string',
            'scope' => 'required|string|in:0,1',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors("Terjadi kesalahan pada data yang anda masukkan");
        }

        $previousSource = explode('|', $request->previous_source);
        if (count($previousSource) !== 2) {
            return redirect()->back()->withErrors('Data sumber salin tidak valid.');
        }

        $params = $request->only([
            'id_audit_periode',
            'id_unit',
            'id_pengisian_panduan',
            'id_jadwal_audit',
            'id_indikator_laporan_kinerja',
            'scope',
        ]);
        $params['previous_year_audit'] = $previousSource[0];
        $params['previous_jadwal_audit'] = $previousSource[1];

        list($success, $response) = $this->service->copyBulkAnswerPeriode($params);


        if (!$success || !is_object($response)) {
            return redirect()->back()->withError($response);
        }

        if ($request->route_name == 'spmi.pengisian-indikator.create') {
            return redirect()->to(route('spmi.pengisian-indikator.edit', ['pengisian_indikator' => $response->data, 'id_indikator_laporan_kinerja' => $request->id_indikator_laporan_kinerja]))->withSuccess($response->message);
        }

        return redirect()->back()->withSuccess($response->message);
    }
}
