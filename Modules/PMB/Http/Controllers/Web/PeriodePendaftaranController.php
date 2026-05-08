<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Date;
use Modules\Core\Helpers\WebController;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\PeriodePendaftaran;
use Modules\PMB\Services\PeriodePendaftaranManagementService;

class PeriodePendaftaranController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PeriodePendaftaranManagementService $service)
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
            ['field' => 'code'],
            ['field' => 'name'],
            ['field' => 'period'],
            ['field' => 'batch'],
            ['field' => 'registration_path'],
            ['field' => 'lecture_system'],
            ['field' => 'registration_type'],
            ['field' => 'is_paid'],
            ['field' => 'status'],
        ];

        return WebController::index($this->service, $request, $header);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), PeriodePendaftaran::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), PeriodePendaftaran::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(int $id)
    {
        $cards = $this->defineFormFields();

        $viewData['sidebar'] = Menu::registrationPeriodSidebar($id);

        return WebController::show($this->service, $id, $cards, PeriodePendaftaran::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), PeriodePendaftaran::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), PeriodePendaftaran::class);
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
            ['title' => 'Data Periode Pendaftaran',
                'subtitle' => 'Informasi detail periode pendaftaran',
                'items' => [
                    ['field' => 'code'],
                    ['field' => 'name'],
                    ['field' => 'description'],
                    ['field' => 'period_id'],
                    ['field' => 'batch_id'],
                    ['field' => 'registration_path_id'],
                    ['field' => 'lecture_system_id'],
                    ['field' => 'registration_type_id'],
                    ['field' => 'status', 'options' => PeriodePendaftaran::STATUSES],
                    ['field' => 'amount_of_program_options',
                        'options' => PeriodePendaftaran::OPTION_AMOUNT_OF_PROGRAMS],
                    ['field' => 'amount_of_program_options_required',
                        'options' => PeriodePendaftaran::OPTION_AMOUNT_OF_PROGRAMS],
                ],
                'edit_url' => url()->current(). '/edit',
            ],
            ['title' => 'Data Waktu Periode Pendaftaran',
                'subtitle' => 'Informasi data tanggal periode pendaftaran',
                'items' => [
                    ['field' => 'opened_at', 'control' => 'datetime', 'required' => true],
                    ['field' => 'closed_at', 'control' => 'datetime', 'required' => true],
                ],
            ],
            ['title' => 'Data Tanggal Daftar Ulang',
                'subtitle' => 'Informasi data tanggal daftar ulang',
                'items' => [
                    ['field' => 'reenrollment_started_at',
                        'control' => 'datetime'],
                    ['field' => 'reenrollment_ended_at',
                        'control' => 'datetime'],
                ],
            ],
            ['title' => 'Data Tanggal Pengumuman',
                'subtitle' => 'Informasi data tanggal pengumuman',
                'items' => [
                    ['field' => 'qualified_announced_at',
                        'control' => 'datetime'],
                    ['field' => 'score_announced_at',
                        'control' => 'datetime'],
                ],
            ],
            ['title' => 'Data Pelengkap',
                'subtitle' => 'Informasi pelengkap periode pendaftaran',
                'items' => [
                    ['field' => 'payment_due_date',
                        'helper' => '*+Hari setelah nomor VA dibuat (max: 14 hari)'],
                    ['field' => 'verification_ended_at', 'control' => 'datetime'],
                    ['field' => 'verification_description'],
                    ['field' => 'start_birth_date'],
                    ['field' => 'last_birth_date'],
                    ['field' => 'last_graduation_year',
                        'options' => Date::getYearOptions()],
                    ['field' => 'report_card_evaluation', 'options' => PeriodePendaftaran::REPORT_EVALUATIONS],
                    ['field' => 'qualification_process', 'options' => PeriodePendaftaran::QUALIFICATION_PROCESSES],
                ],
            ],
            ['title' => 'Opsi Periode Pendaftaran',
                'subtitle' => 'Informasi opsi periode pendaftaran',
                'items' => [
                    ['field' => 'is_paid', 'control' => 'switch'],
                    ['field' => 'is_quota_displayed', 'control' => 'switch'],
                    ['field' => 'is_score_displayed', 'control' => 'switch'],
                    ['field' => 'can_change_program', 'control' => 'switch'],
                    ['field' => 'can_same_program', 'control' => 'switch'],
                    ['field' => 'can_same_faculty', 'control' => 'switch'],
                ],
            ],
        ];
    }
}
