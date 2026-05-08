<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\SyaratPendaftaran;
use Modules\PMB\Models\SyaratJenis;
use Modules\PMB\Services\SyaratPendaftaranManagementService;

class SyaratPendaftaranController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SyaratPendaftaranManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param int $registrationPeriodId
     * @return Renderable
     */
    public function index(Request $request, $registrationPeriodId)
    {
        $header = [
            ['field' => 'assessment_requirement_id'],
            ['field' => 'is_required'],
			['field' => 'is_upload'],
			['field' => 'document_amount'],
        ];

        // filter
        $optRequirementType = SyaratJenis::options();
        $filter = [
            'requirement_type_id' => [ // Syarat Seleksi
                'options' => $optRequirementType, 'label' => __('pmb::requirement_types.main'),
            ],
        ];

        // set default filter
        if (empty($request->filter['requirement_type_id'])) {
            $defaultValue = array_key_first($optRequirementType);
            $request->merge(['filter' => ['requirement_type_id' => $defaultValue]]);
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
        return WebController::create($this->defineFormFields(), SyaratPendaftaran::class);
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

        return WebController::store($this->service, $request, $fields, SyaratPendaftaran::class);
    }

    /**
     * Show the specified resource.
     * @param int $registrationPeriodId
     * @param int $registrationRequirementId
     * @return Renderable
     */
    public function show($registrationPeriodId, $registrationRequirementId)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $registrationRequirementId, $cards, SyaratPendaftaran::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $registrationPeriodId
     * @param int $registrationRequirementId
     * @return Renderable
     */
    public function edit($registrationPeriodId, $registrationRequirementId)
    {
        return WebController::edit(
            $this->service,
            $registrationRequirementId,
            $this->defineFormFields(),
            SyaratPendaftaran::class
        );
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $registrationPeriodId
     * @param int $registrationRequirementId
     * @return Renderable
     */
    public function update(Request $request, $registrationPeriodId, $registrationRequirementId)
    {
        $request->merge(['registration_period_id' => $registrationPeriodId]);
        $fields = $this->defineFormFields();

        // add registration period id to fields
        array_unshift($fields, ['field' => 'registration_period_id']);

        return WebController::update(
            $this->service,
            $registrationRequirementId,
            $request,
            $fields,
            SyaratPendaftaran::class
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $registrationPeriodId
     * @param int $registrationRequirementId
     * @return Renderable
     */
    public function destroy($registrationPeriodId, $registrationRequirementId)
    {
        return WebController::destroy($this->service, $registrationRequirementId);
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
            ['field' => 'assessment_requirement_id'],
            ['field' => 'requirement_type_id'],
            ['field' => 'is_required', 'control' => 'switch'],
			['field' => 'is_upload', 'control' => 'switch'],
			['field' => 'document_amount'],
        ];
    }
}
