<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Keluarga;
use Modules\Core\Services\KeluargaManagementService;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Services\PendaftarManagementService;

class KeluargaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private KeluargaManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request, int $registrantId)
    {
        $header = [
            // ['field' => 'code'],
            ['field' => 'nik'],
            ['field' => 'name'],
            // ['field' => 'birth_place'],
            // ['field' => 'birth_date'],
            ['field' => 'address'],
            // ['field' => 'phone_number'],
            // ['field' => 'gender'],
            ['field' => 'family_status'],
            ['field' => 'degree'],
        ];

        $viewData['sidebar'] = Menu::registrantSidebar($registrantId);

        $this->service->setPersonId($registrantId);

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return WebController::create($this->defineFormFields(), Keluarga::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param int $registrantId
     * @return Renderable
     */
    public function store(Request $request, int $registrantId)
    {
        $personId = (new PendaftarManagementService)->getPersonId($registrantId);
        $request->merge(['person_id' => $personId]);
        $fields = $this->defineFormFields();

        // add person id to fields
        array_unshift($fields, ['field' => 'person_id']);
        return WebController::store($this->service, $request, $fields, Keluarga::class);
    }

    /**
     * Show the specified resource.
     * @param int $registrantId
     * @param int $familyId
     * @return Renderable
     */
    public function show(int $registrantId, int $familyId)
    {
        $cards = $this->defineFormFields();

        $this->service->setPersonId($registrantId);

        return WebController::show($this->service, $familyId, $cards, Keluarga::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $registrantId
     * @param int $familyId
     * @return Renderable
     */
    public function edit(int $registrantId, int $familyId)
    {
        $this->service->setPersonId($registrantId);

        return WebController::edit($this->service, $familyId, $this->defineFormFields(), Keluarga::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @param int $registrantId
     * @param int $familyId
     * @return Renderable
     */
    public function update(Request $request, int $registrantId, int $familyId)
    {
        $personId = (new PendaftarManagementService)->getPersonId($registrantId);
        $request->merge(['person_id' => $personId]);
        $fields = $this->defineFormFields();

        // add person id to fields
        array_unshift($fields, ['field' => 'person_id']);
        return WebController::update($this->service, $familyId, $request, $fields, Keluarga::class);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $registrantId
     * @param int $familyId
     * @return Renderable
     */
    public function destroy(int $registrantId, int $familyId)
    {
        $this->service->setPersonId($registrantId);
        return WebController::destroy($this->service, $familyId);
    }

    // /**
    //  * Remove some resources from storage.
    //  * @param Request $request
    //  * @return Renderable
    //  */
    // public function destroySome(Request $request)
    // {
    //     return WebController::destroySome($this->service, $request);
    // }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            // ['field' => 'code'],
            ['field' => 'nik'],
            ['field' => 'name'],
            ['field' => 'birth_place'],
            ['field' => 'birth_date'],
            ['field' => 'address'],
            ['field' => 'phone_number'],
            ['field' => 'gender', 'control' => 'radio', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            ['field' => 'salary_id'],
            // ['field' => 'person_id'],
            ['field' => 'family_status_id'],
            ['field' => 'job_id'],
            ['field' => 'degree_id'],
        ];
    }
}
