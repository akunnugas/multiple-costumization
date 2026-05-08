<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\ProgramStudi;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Services\Shared\ProgramStudiManagementService;
use Modules\PMB\Helpers\Menu;

class ProgramStudiController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private ProgramStudiManagementService $service)
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
			['field' => 'address'],
			['field' => 'phone_number'],
			['field' => 'sister_code'],
        ];
        $filter = [
            'general_university_id' => ['options' => (new PerguruanTinggi)->options(), 'label' => __('pmb::general_programs.general_university_id')],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('other');
        return WebController::index($this->service, $request, $header, $filter, $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), ProgramStudi::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), ProgramStudi::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, ProgramStudi::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), ProgramStudi::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), ProgramStudi::class);
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
            ['field' => 'code'],
			['field' => 'name'],
			['field' => 'address'],
			['field' => 'phone_number'],
			['field' => 'sister_code'],
            // FIXME: utk testing sementara, nantinya general_university_id itu univ yang login harusnya
            ['field' => 'general_university_id', 'options' => (new PerguruanTinggi)->options()]
        ];
    }
}
