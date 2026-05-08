<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\SeleksiKomposisi;
use Modules\PMB\Services\SeleksiKomposisiManagementService;

/**
 * ! FIXME: controller ini hanya baru CRUD biasa belum menyesuaikan alur yg ada di siakad lama
 * ! dan juga belum ada pengecekan relasi antara $programCompositionId dengan $registrationPeriodId
 */
class SeleksiKomposisiController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SeleksiKomposisiManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request, $registrationPeriodId)
    {
        $header = [
            ['field' => 'assessment_composition_id'],
            ['field' => 'percentage'],
        ];

        // filter
        $optAssessmentType = SeleksiJenis::options();
        $filter = [
            'assessment_type_id' => [ // Jenis Seleksi
                'options' => $optAssessmentType, 'label' => __('pmb::assessment_types.main'),
            ],
        ];

        // set default filter
        if (empty($request->filter['assessment_type_id'])) {
            $defaultValue = array_key_first($optAssessmentType);
            $request->merge(['filter' => ['assessment_type_id' => $defaultValue]]);
        }

        // sidebar
        $viewData['sidebar'] = Menu::registrationPeriodSidebar($registrationPeriodId);

        // set parent resource id
        $this->service->setRegistrationPeriodId($registrationPeriodId);

        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), SeleksiKomposisi::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $registrationPeriodId)
    {
        $request->merge(['registration_period_id' => $registrationPeriodId]);
        $fields = $this->defineFormFields();

        // add registration period id to fields
        array_unshift($fields, ['field' => 'registration_period_id']);

        return WebController::store($this->service, $request, $fields, SeleksiKomposisi::class);
    }

    /**
     * Show the specified resource.
     * @param int $registrationPeriodId
     * @param int $programCompositionId
     * @return Renderable
     */
    public function show($registrationPeriodId, $programCompositionId)
    {
        return WebController::show(
            $this->service,
            $programCompositionId,
            $this->defineFormFields(),
            SeleksiKomposisi::class
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $registrationPeriodId
     * @param int $programCompositionId
     * @return Renderable
     */
    public function edit($registrationPeriodId, $programCompositionId)
    {
        return WebController::edit(
            $this->service,
            $programCompositionId,
            $this->defineFormFields(),
            SeleksiKomposisi::class
        );
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $registrationPeriodId
     * @param int $programCompositionId
     * @return Renderable
     */
    public function update(Request $request, $registrationPeriodId, $programCompositionId)
    {
        return WebController::update(
            $this->service,
            $programCompositionId,
            $request,
            $this->defineFormFields(),
            SeleksiKomposisi::class
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $registrationPeriodId
     * @param int $programCompositionId
     * @return Renderable
     */
    public function destroy($registrationPeriodId, $programCompositionId)
    {
        return WebController::destroy($this->service, $programCompositionId);
    }

//    /**
//     * Remove some resources from storage.
//     * @param Request $request
//     * @return Renderable
//     */
//    public function destroySome(Request $request)
//    {
//        return WebController::destroySome($this->service, $request);
//    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'assessment_type_id'],
            ['field' => 'assessment_composition_id'],
            ['field' => 'percentage'],
        ];
    }
}
