<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Broadcast;
use Modules\Core\Services\BroadcastManagementService;
use Modules\PMB\Helpers\Menu;

class BroadcastController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private BroadcastManagementService $service)
    {
        //
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'title'],
			['field' => 'amount_of_receiver'],
			['field' => 'status'],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('reference-announcement');

        $this->service->setModuleType(Broadcast::MODULE_TYPE_PMB);

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), Broadcast::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $fields = $this->defineFormFields();

        // append module_type PMB
        $request->merge(['module_type' => Broadcast::MODULE_TYPE_PMB]);
        $fields[] = ['field' => 'module_type', 'type' => 'hidden'];

        return WebController::store($this->service, $request, $fields, Broadcast::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFieldsForDetail();

        // sidebar
        $viewData['sidebar'] = Menu::broadcastSidebar($id);

        return WebController::show($this->service, $id, $cards, Broadcast::class, viewData: $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request, $id)
    {
        // cards
        $cards = $this->defineFormFields();

        return WebController::edit($this->service, $id, $cards, Broadcast::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $fields = $this->defineFormFields();

        return WebController::update($this->service, $id, $request, $fields, Broadcast::class);
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

    // FIXME: nonaktifkan multiple destroy
    /**
     * Remove some resources from storage.
     * @param Request $request
     * @return Renderable
     */
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
            ['field' => 'title'],
			['field' => 'content', 'control' => 'textarea'],
			['field' => 'is_email', 'control' => 'switch'],
			['field' => 'is_sms', 'control' => 'switch'],
			['field' => 'is_whatsapp', 'control' => 'switch'],
        ];
    }

    private function defineFormFieldsForDetail()
    {
        return [
            'broadcast' => ['title' => 'Data Broadcast',
                'edit_url' => url()->current(). '/edit?sub=broadcast',
                'items' => [
                    ['field' => 'title'],
                    ['field' => 'content', 'control' => 'textarea'],
                ],
            ],
            'information-sending' => ['title' => 'Informasi Pengiriman',
                'items' => [
                    ['field' => 'is_email', 'control' => 'switch'],
                    ['field' => 'is_sms', 'control' => 'switch'],
                    ['field' => 'is_whatsapp', 'control' => 'switch'],
                    ['field' => 'amount_of_receiver', 'type' => 'hidden'],
                    ['field' => 'amount_of_email_sent', 'type' => 'hidden'],
                    ['field' => 'amount_of_sms_sent', 'type' => 'hidden'],
                    ['field' => 'amount_of_whatsapp_sent', 'type' => 'hidden'],
                ]
            ]
        ];
    }
}
