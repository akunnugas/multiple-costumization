<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\JenisInstitusi;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\SebaranAsalPendaftar;
use Modules\PMB\Models\SebaranPilihan;
use Modules\PMB\Services\SebaranProdiManagementService;
use Modules\PMB\Services\PeriodePendaftaranManagementService;


/**
 * ! FIXME: controller ini hanya baru CRUD biasa belum menyesuaikan alur yg ada di siakad lama
 * ! dan juga belum ada pengecekan relasi antara $programDistributionId dengan $registrationPeriodId
 */
class SebaranProdiController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SebaranProdiManagementService $service)
    {
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
            ['field' => 'organization_id'],
            ['field' => 'quota'],
			['field' => 'minimum_score'],
			['field' => 'nim_prefix'],
        ];

        $viewData['sidebar'] = Menu::registrationPeriodSidebar($registrationPeriodId);

        $this->service->setRegistrationPeriodId($registrationPeriodId);

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(int $registrationPeriodId)
    {
        $cards = $this->defineFormFields($registrationPeriodId);

        return WebController::create($cards, SebaranProdi::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, int $registrationPeriodId)
    {
        $request->merge(['registration_period_id' => $registrationPeriodId]);
        $fields = $this->defineFormFields($registrationPeriodId);

        // add registration period id to fields
        array_unshift($fields['data-main']['items'], ['field' => 'registration_period_id']);

        return WebController::store(
            $this->service,
            $request,
            $fields,
            [SebaranProdi::class, SebaranAsalPendaftar::class, SebaranPilihan::class]
        );
    }

    /**
     * Show the specified resource.
     * @param int $registrationPeriodId
     * @param int $programDistributionId
     * @return Renderable
     */
    public function show(int $registrationPeriodId, int $programDistributionId)
    {
        $cards = $this->defineFormFields($registrationPeriodId);

        return WebController::show(
            $this->service,
            $programDistributionId,
            $cards,
            [SebaranProdi::class]
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $registrationPeriodId
     * @param int $programDistributionId
     * @return Renderable
     */
    public function edit(int $registrationPeriodId, int $programDistributionId)
    {
        return WebController::edit(
            $this->service,
            $programDistributionId,
            $this->defineFormFields($registrationPeriodId),
            SebaranProdi::class
        );
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $registrationPeriodId
     * @param int $programDistributionId
     * @return Renderable
     */
    public function update(Request $request, int $registrationPeriodId, int $programDistributionId)
    {
        $request->merge(['registration_period_id' => $registrationPeriodId]);
        $fields = $this->defineFormFields($registrationPeriodId);

        // add registration period id to fields
        array_unshift($fields['data-main']['items'], ['field' => 'registration_period_id']);

        return WebController::update(
            $this->service,
            $programDistributionId,
            $request,
            $fields,
            [SebaranProdi::class, SebaranAsalPendaftar::class, SebaranPilihan::class]
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $registrationPeriodId
     * @param int $programDistributionId
     * @return Renderable
     */
    public function destroy(int $registrationPeriodId, int $programDistributionId)
    {
        return WebController::destroy($this->service, $programDistributionId);
    }

    /**
     * Form input.
     * @param int $registrationPeriodId
     * @return array
     */
    private function defineFormFields(int $registrationPeriodId): array
    {
        $fields = [
            'data-main' => ['title' => 'Data Sebaran Program Studi',
                'items' => [
                    ['field' => 'organization_id'],
                    ['field' => 'quota'],
                    ['field' => 'minimum_score'],
                    ['field' => 'nim_prefix'],
                    ['field' => 'nim_maximal_digit'],
                ],
                'edit_url' => url()->current(). '/edit',
            ],
            'data-option' => ['title' => 'Sebaran Pilihan',
                'items' => [],
            ],
            'data-institution-type' => ['title' => 'Sebaran Asal Pendaftar',
                'items' => [],
            ],
        ];

        // get options
        $registrationPeriod = (new PeriodePendaftaranManagementService())->show($registrationPeriodId)->toArray();
        $options = $registrationPeriod['amount_of_program_options'];

        // looping utk mapping ke data-option items di atas
        for ($i = 1; $i <= $options; $i++) {
            $fields['data-option']['items'][] = [
                'field' => 'program_option_' . $i,
                'label' => 'Pilihan ' . $i . '?',
                'control' => 'switch',
                'type' => 'boolean',
            ];
        }

        // get instution type
        $institutionType = JenisInstitusi::optionNonUniversity();

        // looping utk mapping ke data-institution-type items di atas
        foreach ($institutionType as $key => $value) {
            $fields['data-institution-type']['items'][] = [
                'field' => 'program_institution_' . $key,
                'label' => $value . '?',
                'control' => 'switch',
                'type' => 'boolean',
            ];
        }

        return $fields;
    }
}
