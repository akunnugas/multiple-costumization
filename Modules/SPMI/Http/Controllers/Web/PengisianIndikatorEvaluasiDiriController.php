<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
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
use Modules\SPMI\Models\DataPengisianLED;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\AuditPeriodeManagementService;
use Modules\SPMI\Services\IndikatorEvaluasiDiriManagementService;
use Modules\SPMI\Services\PengisianIndikatorEvaluasiDiriManagementService;

class PengisianIndikatorEvaluasiDiriController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PengisianIndikatorEvaluasiDiriManagementService $service)
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
            ['field' => 'periode_audit', 'label' => 'Periode AMI', 'searchable' => false],
            ['field' => 'nama_jadwal_audit', 'label' => 'Nama Kegiatan AMI'],
            ['field' => 'nama_unit', 'label' => 'Unit / Program Studi', 'order' => 'asc'],
            ['field' => 'nama_lembaga_akreditasi'],
            ['field' => 'nama_pengisian_panduan', 'label' => 'Panduan Pengisian Evaluasi Diri'],
            ['field' => 'status_pengisian_indikator', 'component' => true, 'searchable' => false],
            ['field' => 'action', 'component' => 'pengisian_indikator', 'searchable' => false]
        ];

        $optAuditPeriode = AuditPeriode::options();
        $optJenjangPendidikan = JenjangPendidikan::options();
        $optUnitKerja = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);

        $idAuditPeriode = $request->filter['id_audit_periode'] ?? session('filter_pengisian_indikator_led_periode') ?? array_key_first($optAuditPeriode);
        $idJenjangPendidikan = $request->filter['id_jenjang_pendidikan'] ?? session('filter_pengisian_indikator_led_jenjang_pendidikan') ?? null;
        $idUnitKerja = $request->filter['id_unit_kerja'] ?? session('filter_pengisian_indikator_led_unit_kerja') ?? null;

        if (!empty($idAuditPeriode)) {
            session(['filter_pengisian_indikator_led_periode' => $idAuditPeriode]);
        }
        if (!empty($idJenjangPendidikan)) {
            session(['filter_pengisian_indikator_led_jenjang_pendidikan' => $idJenjangPendidikan]);
        }
        if (!empty($idUnitKerja)) {
            session(['filter_pengisian_indikator_led_unit_kerja' => $idUnitKerja]);
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
                'title' => 'Belum Ada Data Pengisian Evaluasi Diri',
                'subtitle' => 'Silakan tambahkan data pengisian laporan evaluasi diri dengan cara memetakan unit dengan panduan akreditasi di <a class="a-link" href="' . route('spmi.unit-kerja.index') . '">halaman unit</a>'
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
        $fillingIndicator = PengisianIndikator::findByStudyProgramIdAndType($yearData->id, $request->id_pengisian_panduan, $request->id_unit, AkreditasiBuku::SELF_EVALUATION, $request->id_jadwal_audit);

        // if filling indicator has found or audit year has found
        if ($fillingIndicator) {
            return redirect()->route('spmi.pengisian-indikator-led.edit', [$fillingIndicator->id]);
        }

        // declare url for create
        $URLCreate = Page::createURL() . "?id_unit={$request->id_unit}&periode_audit={$request->periode_audit}&id_pengisian_panduan={$request->id_pengisian_panduan}&id_jadwal_audit={$request->id_jadwal_audit}";

        // create tree view
        $indicatorTree = (new IndikatorEvaluasiDiriManagementService)->showAllIndicatorsByPengisianPanduan(
            $request->id_pengisian_panduan,
            $yearData?->id,
            $request->id_unit,
            true,
            $URLCreate . "&id_indikator_evaluasi_diri="
        );

        // get child active
        if (!empty($request->id_indikator_evaluasi_diri)) {
            $childActive = IndikatorEvaluasiDiri::find($request->id_indikator_evaluasi_diri)->toArray(['info_left', 'info_right']);
        }

        $htmlTree = UI::createTreeView($indicatorTree, $childActive ?? null);

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
        $raw['id_indikator_evaluasi_diri'] = $request->id_indikator_evaluasi_diri;
        $raw['id_pengisian_panduan'] = $request->id_pengisian_panduan ?? $raw['id_pengisian_panduan'];

        // generate table with generator on indicator performance report service
        $table = '';
        // if indicator performance report id is not empty
        if (!empty($request->id_indikator_evaluasi_diri)) {
            // get indicator performance report
            $indicatorSelfEvaluation = IndikatorEvaluasiDiri::find($request->id_indikator_evaluasi_diri);
            $data = $indicatorSelfEvaluation->toArray();

            $cond = [];
            $cond['year'] = $request->periode_audit;

            $isEditAll = false;
            if (!empty($request->get('is_edit'))) {
                $isEditAll = true;
            }

            // generate table
            $table = (new IndikatorEvaluasiDiriManagementService)->generateTable($request->id_indikator_evaluasi_diri, $cond, isEditAll: $isEditAll, isPreview: !$request->permission['post'] ?? false);
        } else {
            $data = [
                'name' => 'Indentitas Pengusul'
            ];
        }

        $data['previous_year_audit'] = [];
        $data['audit_period_label'] = '';
        list($success, $prevYearAudit) = $this->service->getPreviousSelectOptions(
            idAuditPeriode: $raw['id_audit_periode'],
            idUnit: $raw['id_unit'],
            idPengisianPanduan: $raw['id_pengisian_panduan'],
        );
        $auditPeriod = (new AuditPeriodeManagementService())->show((int) $raw['id_audit_periode']);
        if ($auditPeriod) {
            $data['audit_period_label'] = $auditPeriod->tahun_audit;
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
            $data['previous_year_audit'] = $prevYearAuditOptions;
        }

        if (empty($data['id_pengisian_panduan'])) {
            abort(404);
        }

        $pengisianPanduan = PengisianPanduan::findFirstRefrence($data['id_pengisian_panduan'])->toArray();

        return WebController::buildView('create')
            ->withData([
                'sidebar' => $htmlTree,
                'information' => $information,
                'link_proposing_team' => $URLCreate,
                'data' => $data,
                'table' => $table,
                'raw' => $raw,
                'pengisian_panduan' => $pengisianPanduan,
                'apakah_tanggal_pengisian_valid' => $apakahTanggalPengisianValid,
                'apakah_pengisian_belum_dimulai' => $apakahPengisianBelumDimulai,
                'apakah_bisa_aksi' => $request->permission['post'] ?? false,
            ]);
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

        $fillingIndicator = PengisianIndikator::findByStudyProgramIdAndType($periodeAuditId, $idPengisianPanduan, $studyProgramId, AkreditasiBuku::SELF_EVALUATION, $request->self['id_jadwal_audit'] ?? null);
        if ($fillingIndicator) {
            $fillingIndicatorData = DataPengisianLED::where('id_pengisian_indikator', $fillingIndicator->id)
                ->where('id_indikator_evaluasi_diri', $request->self['id_indikator_evaluasi_diri'])
                ->first();

            if ($fillingIndicatorData) {
                $newSelf = $request->self;
                $newSelf['filling_indicator_data_id'] = $fillingIndicatorData->id;
                $request->merge([
                    'self' => $newSelf
                ]);

                return $this->update($request, $fillingIndicator->id);
            }
        }

        $return = $this->service->store($request->all());

        if ($request->act === 'upload-dokumen-pendukung') {
            $successMessage = 'Berhasil mengupload dokumen pendukung';
        }

        if (Error::isError($return)) {
            return $return->redirectBack()->withError($return->message);
        } else {
            // redirect on spesific indicator performance report
            $id = $return->id;
            $url = Page::editURL($id);

            if (!empty($request->self['id_indikator_evaluasi_diri'])) {
                $url .= "?id_indikator_evaluasi_diri={$request->self['id_indikator_evaluasi_diri']}";
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
        $dokumenPendukung = [];

        if (Error::isError($data)) {
            if ($data->code == 404) {
                abort(404);
            }
            return $data->redirectBack();
        }

        // declare information and raw data
        [$information, $raw, $apakahTanggalPengisianValid, $apakahPengisianBelumDimulai] = $data;

        // declare url for edit
        $editURL = Page::editURL($id);
        // create tree view
        $indicatorTree = (new IndikatorEvaluasiDiriManagementService)->showAllIndicatorsByPengisianPanduan(
            $raw['id_pengisian_panduan'],
            $raw['id_audit_periode'],
            $raw['id_unit'],
            true,
            $editURL . "?id_indikator_evaluasi_diri="
        );
        // get child active
        if (!empty($request->id_indikator_evaluasi_diri)) {
            $childActive = IndikatorEvaluasiDiri::find($request->id_indikator_evaluasi_diri)->toArray(['info_left', 'info_right']);
        }

        $htmlTree = UI::createTreeView($indicatorTree, $childActive ?? null);

        // generate table with generator on indicator performance report service
        $table = '';
        if (!empty($request->id_indikator_evaluasi_diri)) {
            // get indicator performance report
            $indicatorSelfEvaluation = IndikatorEvaluasiDiri::find($request->id_indikator_evaluasi_diri);
            $data = $indicatorSelfEvaluation->toArray();

            // get records from filling indicator data
            $records = [];
            $records = DataPengisianLED::where('id_pengisian_indikator', $id)
                ->where('id_indikator_evaluasi_diri', $request->id_indikator_evaluasi_diri)
                ->first();

            if ($records) {
                $records = $records->toArray();
                $raw['filling_indicator_data_id'] = $records['id'];
            }


            // add indicator performance report id to raw data
            $raw['id_indikator_evaluasi_diri'] = $request->id_indikator_evaluasi_diri;

            if (!empty($records['key_points']))
                $records['key_points'] = json_decode($records['key_points']);

            $isEditAll = false;
            if (!empty($request->get('is_edit'))) {
                $isEditAll = true;
            }

            $cond = [];
            $cond['year'] = $information['periode_audit'];

            // generate table with generator on indicator performance report service
            $table = (new IndikatorEvaluasiDiriManagementService)->generateTable($request->id_indikator_evaluasi_diri, $cond, $records, $isEditAll, isPreview: !$request->permission['put'] ?? false);

            $dokumenPendukung = $this->service->getDokumenPendukung($id, $request->id_indikator_evaluasi_diri);
        } else {
            $data = [
                'name' => 'Indentitas Pengusul'
            ];
        }

        $data['previous_year_audit'] = [];
        $data['audit_period_label'] = '';
        list($success, $prevYearAudit) = $this->service->getPreviousSelectOptions(
            idAuditPeriode: $raw['id_audit_periode'],
            idUnit: $raw['id_unit'],
            idPengisianPanduan: $raw['id_pengisian_panduan'],
        );
        $auditPeriod = (new AuditPeriodeManagementService())->show((int) $raw['id_audit_periode']);
        if ($auditPeriod) {
            $data['audit_period_label'] = $auditPeriod->tahun_audit;
        }

        if ($success && $prevYearAudit && $auditPeriod) {
            $prevYearAuditOptions = [];
            foreach ($prevYearAudit as $item) {
                $key = $item->id . '|' . $item->id_jadwal_audit;
                $label = $item->tahun_audit;
                if (!empty($item->nama_jadwal_audit)) {
                    $label .= ' - ' . $item->nama_jadwal_audit;
                }
                $prevYearAuditOptions[$key] = $label;
            }
            $data['previous_year_audit'] = $prevYearAuditOptions;
        }

        if (empty($data['id_pengisian_panduan'])) {
            abort(404);
        }

        $pengisianPanduan = PengisianPanduan::findFirstRefrence($data['id_pengisian_panduan'])->toArray();

        return WebController::buildView('create')
            ->withData([
                'sidebar' => $htmlTree,
                'information' => $information,
                'link_proposing_team' => $editURL,
                'data' => $data,
                'table' => $table,
                'raw' => $raw,
                'records' => $records ?? [],
                'pengisian_panduan' => $pengisianPanduan,
                'apakah_tanggal_pengisian_valid' => $apakahTanggalPengisianValid,
                'apakah_pengisian_belum_dimulai' => $apakahPengisianBelumDimulai,
                'apakah_bisa_aksi' => $request->permission['put'] ?? false,
                'dokumen_pendukung' => $dokumenPendukung
            ])->withResourceId($id);
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

        if (empty($id) || $invalidParam) {
            return redirect()->route('spmi.pengisian-indikator.index')->withError('Data tidak ditemukan');
        }

        $return = $this->service->update($request->all(), $id);

        if ($request->act === 'upload-dokumen-pendukung') {
            $successMessage = 'Berhasil mengupload dokumen pendukung';
        }

        if (Error::isError($return)) {
            return $return->redirectBack()->withError($return->message);
        } else {
            // redirect on spesific indicator performance report
            $url = Page::editURL($id);

            if (!empty($request->self['id_indikator_evaluasi_diri'])) {
                $url .= "?id_indikator_evaluasi_diri={$request->self['id_indikator_evaluasi_diri']}";
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

    public function deleteDokumen($id, Request $request, $idDokumen)
    {
        $return = $this->service->deleteDokumenPendukung($id, $idDokumen);

        if (Error::isError($return)) {
            return $return->redirectBack()->withError($return->message);
        } else {
            $url = route('spmi.pengisian-indikator-led.edit', $id);

            if (!empty($return->id_indikator_evaluasi_diri)) {
                $url .= "?id_indikator_evaluasi_diri={$return->id_indikator_evaluasi_diri}";
            }

            return redirect()->to($url)->withSuccess('Berhasil menghapus dokumen pendukung');
        }
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
     * show report
     */
    public function showReport()
    {
        $raw = $_POST['self'];
        $data = $this->service->showReport($raw['id_pengisian_panduan']);
        $auditPeriod = AuditPeriode::find($raw['id_audit_periode']);
        $studyProgram = UnitKerja::find($raw['id_unit']);

        $records =  PengisianIndikator::getRecordByFilter($raw['id_audit_periode'], $raw['accreditation_agency_id'], $raw['id_pengisian_panduan'], $raw['id_unit'], isLED: true, idJadwalAudit: $raw['id_jadwal_audit'] ?? null);

        $PengisianPanduan = PengisianPanduan::find($raw['id_pengisian_panduan']);
        $title = $PengisianPanduan->name;

        $information = [];
        $information['periode_audit'] = $auditPeriod->year;
        $information['study_program'] =  $studyProgram->degree->code . ' ' . $studyProgram->name;

        return view('spmi::pages.pengisian-indikator-led.report', compact('data', 'information', 'title', 'records'));
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
            'id_indikator_evaluasi_diri',
            'scope',
        ]);
        $params['previous_year_audit'] = $previousSource[0];
        $params['previous_jadwal_audit'] = $previousSource[1];

        list($success, $response) = $this->service->copyBulkAnswerPeriode($params);


        if (!$success || !is_object($response)) {
            return redirect()->back()->withError($response);
        }

        if ($request->route_name == 'spmi.pengisian-indikator-led.create') {
            return redirect()->to(route('spmi.pengisian-indikator-led.edit', ['pengisian_indikator_led' => $response->data, 'id_indikator_evaluasi_diri' => $request->id_indikator_evaluasi_diri]))->withSuccess($response->message);
        }

        return redirect()->back()->withSuccess($response->message);
    }
}
