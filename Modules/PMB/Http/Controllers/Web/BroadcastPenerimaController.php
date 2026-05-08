<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\BroadcastPenerima;
use Modules\Core\Services\BroadcastPenerimaManagementService;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\PeriodePendaftaran;

class BroadcastPenerimaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private BroadcastPenerimaManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request, $broadcastId)
    {
        $header = [
            ['field' => 'recipient_code'],
            ['field' => 'recipient_name'],
            ['field' => 'recipient_type', 'options' => BroadcastPenerima::TYPES],
            ['field' => 'is_email_sent'],
            ['field' => 'is_whatsapp_sent'],
            ['field' => 'is_sms_sent'],
        ];

        $this->service->setBroadcastId($broadcastId);

        // sidebar
        $viewData['sidebar'] = Menu::broadcastSidebar($broadcastId);

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $cards = $this->defineFormFields();

        return WebController::create($cards, BroadcastPenerima::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), BroadcastPenerima::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, BroadcastPenerima::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), BroadcastPenerima::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), BroadcastPenerima::class);
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
        // TODO: harusnya ini dinamis penerimanya
        $recipientTypeOptions = BroadcastPenerima::TYPE_OF_MODULE_PMB;
        $recipientOptions = BroadcastPenerima::getSearchRecipientOptions(BroadcastPenerima::TYPE_PENDAFTAR);
        $registrationPeriodOptions = PeriodePendaftaran::options();

        return [
            ['field' => 'recipient_type', 'required' => true, 'options' => $recipientTypeOptions],
            ['field' => 'registration_period_id', 'options' => $registrationPeriodOptions],
            ['field' => 'recipients', 'required' => true, 'options' => $recipientOptions],
        ];
    }
}
