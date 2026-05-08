<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Services\AgendaKegiatanService;
use Modules\Litabmas\Services\SumberPendanaanService;

class SumberPendanaanController extends Controller
{
    protected AgendaKegiatanService $agendaKegiatanService;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SumberPendanaanService $service)
    {
        $this->agendaKegiatanService = new AgendaKegiatanService();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $headers = [
            ['field' => 'tahun_periode_pendanaan', 'name' => 'id_periode_pendanaan', 'options' => PeriodePendanaan::class,
                'label' => 'Periode Pendanaan'],
            ['field' => 'nama_sumber_pendanaan'],
            ['field' => 'nama_unit', 'name' => 'id_unit_kerja',
                'options' => UnitKerja::optionByType([UnitKerja::UNIVERSITY, UnitKerja::FACULTY], false),
                'label' => 'Pengelola Pendanaan'],
            ['field' => 'kategori_sumber_pendanaan', 'options' => SumberPendanaan::CATEGORIES, 'searchable' => false],
            ['field' => 'total_pendanaan', 'component' => 'format_currency', 'styleAlign' => 'right'],
        ];

        $filter = [
            'kategori_sumber_pendanaan' => [
                'options' => ['' => '-- Semua ' . __('litabmas::sumber_pendanaan.kategori_sumber_pendanaan') . ' --'] + SumberPendanaan::CATEGORIES,
                'hideLabel' => true,
            ],
        ];

        $viewData = [
            'title' => 'Sumber Pendanaan & Penentuan Tahapan Kegiatan',
            'showDeleteChecked' => false,
            'showNumber' => true,
        ];

        return WebController::index($this->service, $request, $headers, filter: $filter, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $sumberPendanaanFields = $this->defineFormFields();
        $fieldPeriodePendanaan = $sumberPendanaanFields[0]; // field tahun_periode_pendanaan
        $periodeAktif = PeriodePendanaan::periodeAktif();
        // tambahkan label (aktif) jika periode aktif di option
        if (!empty($periodeAktif)) {
            $fieldPeriodePendanaan['options'][$periodeAktif->id] = $periodeAktif->tahun . ' (Periode Aktif)';
        }
        $sumberPendanaanFields[0] = $fieldPeriodePendanaan;

        $agendaKegiatan = $this->agendaKegiatanService->getListCache(); // daftar master agenda kegiatan

        $viewData['title'] = 'Tambah Sumber Pendanaan & Penentuan Tahapan Kegiatan';

        return WebController::create($sumberPendanaanFields, SumberPendanaan::class, viewData: $viewData)
            ->with('sourceAgendaMappings', $agendaKegiatan);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // ditambah inputan dari blade yaitu optional_agenda_ids agar bisa diolah di service
        $formFields = $this->defineFormFields();
        $formFields[] = ['field' => 'optional_agenda_ids'];

        $messages = [
            'total_pendanaan.min' => __('litabmas::sumber_pendanaan.total_pendanaan') . ' tidak boleh kurang dari 0',
        ];

        return WebController::store(
            $this->service,
            $request,
            $formFields,
            SumberPendanaan::class,
            messageValidation: $messages
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        WebController::validate(id: $id);

        $cards = $this->defineFormFields();

        $sourceAgendaMapping = $this->service->getAktifAgendaKegiatanByIdSumberPendanaan($id); // mappingan agenda yg aktif

        $viewData['isDetailV2'] = true;

        $viewData['page_conf'][] = ['custom_page' => ['component' => 'custom-show', 'data' => [
            'sourceAgendaMapping' => $sourceAgendaMapping
        ]]];

        return WebController::show($this->service, $id, $cards, SumberPendanaan::class, viewData: $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        WebController::validate(id: $id);

        $sumberPendanaanFields = $this->defineFormFields();
        $fieldPeriodePendanaan = $sumberPendanaanFields[0]; // field tahun_periode_pendanaan
        $periodeAktif = PeriodePendanaan::periodeAktif();
        // tambahkan label (aktif) jika periode aktif di option
        if (!empty($periodeAktif)) {
            $fieldPeriodePendanaan['options'][$periodeAktif->id] = $periodeAktif->tahun . ' (Periode Aktif)';
        }
        $sumberPendanaanFields[0] = $fieldPeriodePendanaan;
        $agendaKegiatan = $this->agendaKegiatanService->getListCache(); // daftar master agenda kegiatan
        $sourceAgendaMapping = $this->service->getAktifAgendaKegiatanByIdSumberPendanaan($id); // mappingan agenda yg aktif

        if (!empty($sourceAgendaMapping)) {
            // set is_checked jika sudah terdaftar di mapping
            foreach ($agendaKegiatan as $agenda) {
                $isChecked = $sourceAgendaMapping->contains('id_agenda_kegiatan', $agenda->id);
                $agenda->is_checked = $isChecked;
            }
        }

        $isEdit = !KlasterPendanaan::where('id_sumber_pendanaan', $id)->exists();

        $viewData['isEdit'] = $isEdit;

        $viewData['title'] = 'Edit Sumber Pendanaan & Penentuan Tahapan Kegiatan';

        return WebController::edit($this->service, $id, $sumberPendanaanFields, SumberPendanaan::class, viewData: $viewData)
            ->with('sourceAgendaMappings', $agendaKegiatan);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $formFields = $this->defineFormFields();
        // ditambah inputan dari blade yaitu optional_agenda_ids agar bisa diolah di service
        $formFields[] = ['field' => 'optional_agenda_ids'];

        $messages = [
            'total_pendanaan.min' => __('litabmas::sumber_pendanaan.total_pendanaan') . ' tidak boleh kurang dari 0',
        ];

        return WebController::update(
            $this->service,
            $id,
            $request,
            $formFields,
            SumberPendanaan::class,
            messageValidation: $messages
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
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'tahun_periode_pendanaan', 'name' => 'id_periode_pendanaan', 'options' => PeriodePendanaan::options(),
                'label' => 'Periode Pendanaan', 'required' => true],
            ['field' => 'nama_sumber_pendanaan'],
            ['field' => 'nama_unit', 'name' => 'id_unit_kerja', 'required' => true,
                'options' => UnitKerja::optionByType([UnitKerja::UNIVERSITY, UnitKerja::FACULTY], false),
                'label' => 'Pengelola Pendanaan'],
            ['field' => 'kategori_sumber_pendanaan', 'options' => SumberPendanaan::CATEGORIES],
            // ['field' => 'mata_uang'],
            ['field' => 'total_pendanaan', 'currency_field' => 'mata_uang', 'control' => 'currency'], // currency_field adalah field yang menyimpan kode mata uang
            ['field' => 'maksimal_toleransi_similarity', 'type' => 'number', 'data-number-max' => 100, 'data-number-float' => true,
                'helper' => 'Maksimal toleransi similarity dihitung dalam persen'],
            ['field' => 'maksimal_toleransi_ai', 'type' => 'number', 'data-number-max' => 100, 'data-number-float' => true,
                'helper' => 'Maksimal toleransi AI dihitung dalam persen'],
        ];
    }
}
