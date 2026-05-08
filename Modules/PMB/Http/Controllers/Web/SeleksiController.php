<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\Seleksi;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Services\SeleksiJenisManagementService;
use Modules\PMB\Services\SeleksiManagementService;
use Modules\PMB\Services\SebaranProdiManagementService;

class SeleksiController extends Controller
{
    protected SeleksiJenisManagementService $assessmentTypeService;

    protected SebaranProdiManagementService $programDistributionService;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SeleksiManagementService $service)
    {
        $this->assessmentTypeService = new SeleksiJenisManagementService;
        $this->programDistributionService = new SebaranProdiManagementService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param int $registrationPeriodId
     * @return Renderable
     */
    public function index(Request $request, int $registrationPeriodId)
    {
        $header = [
            ['field' => 'program_distribution_id'],
            ['field' => 'assessment_order'],
			['field' => 'score_percentage'],
			['field' => 'started_at'],
			['field' => 'ended_at'],
        ];

        // filter
        $labelProgramDistribution = __('pmb::registration_periods/program_distributions.main');
        $optProgramDistribution = ['-- Semua ' . $labelProgramDistribution . ' --'] + SebaranProdi::optionStudyPrograms($registrationPeriodId);
        $filter = [
            'program_distribution_id' => [ // Jenis Seleksi
                'options' => $optProgramDistribution, 'label' => $labelProgramDistribution,
            ],
        ];

        // sidebar
        $viewData['sidebar'] = Menu::registrationPeriodSidebar($registrationPeriodId);

        // set parent resource id
        $this->service->setParentResourceId($registrationPeriodId);

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @param int $registrationPeriodId
     * @return Renderable
     */
    public function create(int $registrationPeriodId)
    {
        return WebController::create($this->defineFormFields($registrationPeriodId), Seleksi::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param int $registrationPeriodId
     * @return Renderable
     */
    public function store(Request $request, int $registrationPeriodId)
    {
        return WebController::store(
            $this->service,
            $request,
            $this->defineFormFields($registrationPeriodId),
            Seleksi::class
        );
    }

    /**
     * Show the specified resource.
     * @param int $registrationPeriodId
     * @param int $programAssessmentId
     * @return Renderable
     */
    public function show(int $registrationPeriodId, int $programAssessmentId)
    {
        return WebController::show(
            $this->service,
            $programAssessmentId,
            $this->defineFormFields($registrationPeriodId),
            Seleksi::class
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $registrationPeriodId
     * @param int $programAssessmentId
     * @return Renderable
     */
    public function edit(int $registrationPeriodId, int $programAssessmentId)
    {
        return WebController::edit(
            $this->service,
            $programAssessmentId,
            $this->defineFormFields($registrationPeriodId),
            Seleksi::class
        );
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $registrationPeriodId
     * @param int $programAssessmentId
     * @return Renderable
     */
    public function update(Request $request, int $registrationPeriodId, int $programAssessmentId)
    {
        return WebController::update(
            $this->service,
            $programAssessmentId,
            $request,
            $this->defineFormFields($registrationPeriodId),
            Seleksi::class
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $registrationPeriodId
     * @param int $programAssessmentId
     * @return Renderable
     */
    public function destroy(int $registrationPeriodId, int $programAssessmentId)
    {
        return WebController::destroy($this->service, $programAssessmentId);
    }

    /**
     * Form input.
     * @param int|null $registrationPeriodId
     * @return array
     */
    private function defineFormFields(int $registrationPeriodId = null): array
    {
        $programDistributionOptions = SebaranProdi::optionStudyPrograms($registrationPeriodId);
        $assessmentOrderOptions = SeleksiJenis::optionsAmount();

        return [
            ['field' => 'program_distribution_id', 'options' => $programDistributionOptions],
            ['field' => 'assessment_type_id'],
            ['field' => 'assessment_order', 'options' => $assessmentOrderOptions],
			['field' => 'score_percentage'],
			['field' => 'started_at', 'control' => 'datetime'],
			['field' => 'ended_at', 'control' => 'datetime'],
        ];
    }
}
