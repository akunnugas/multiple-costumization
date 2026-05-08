<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\Sekolah;
use Modules\Core\Services\WilayahManagementService;
use Modules\Core\Services\SekolahManagementService;
use Modules\PMB\Helpers\Menu;

class SekolahController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private SekolahManagementService $service)
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
            // ['field' => 'institution_type_id'],
            ['field' => 'name'],
            // ['field' => 'address'],
            // ['field' => 'rt'],
            // ['field' => 'rw'],
            // ['field' => 'postal_code'],
            // ['field' => 'phone_number'],
            // ['field' => 'email'],
            // ['field' => 'website'],
            ['field' => 'accreditation'],
            ['field' => 'city_id'],
        ];
        $filter = [
            'city_id' => ['options' => (new WilayahManagementService)->options(Wilayah::LEVEL_CITY), 'label' => __('pmb::schools.city_id')],
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
        return WebController::create($this->defineFormFields(), Sekolah::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), Sekolah::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, Sekolah::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(), Sekolah::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), Sekolah::class);
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
            ['field' => 'city_id'],
            ['field' => 'institution_type_id'],
            ['field' => 'name'],
            ['field' => 'address'],
            ['field' => 'rt'],
            ['field' => 'rw'],
            ['field' => 'postal_code'],
            ['field' => 'phone_number'],
            ['field' => 'email'],
            ['field' => 'website'],
            ['field' => 'accreditation'],
        ];
    }
}
