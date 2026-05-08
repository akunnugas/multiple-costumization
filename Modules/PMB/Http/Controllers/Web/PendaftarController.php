<?php

namespace Modules\PMB\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Biodata;
use Modules\PMB\Helpers\Menu;
use Modules\PMB\Models\Pendaftar;
use Modules\PMB\Services\PendaftarManagementService;

class PendaftarController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PendaftarManagementService $service)
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
            ['field' => 'gender'],
        ];

        return WebController::index($this->service, $request, $header);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $model = [Pendaftar::class, Biodata::class];

        return WebController::create($this->defineFormFields(), $model);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $model = [Pendaftar::class, Biodata::class];
        return WebController::store($this->service, $request, $this->defineFormFields(), $model);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $model = [Pendaftar::class, Biodata::class];
        $cards = $this->defineFormFields();

        $viewData['sidebar'] = Menu::registrantSidebar($id);

        return WebController::show($this->service, $id, $cards, $model, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $model = [Pendaftar::class, Biodata::class];
        return WebController::edit($this->service, $id, $this->defineFormFields(), $model);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $model = [Pendaftar::class, Biodata::class];
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), $model);
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
            ['title' => 'Data Pendaftar',
                'subtitle' => 'Informasi detail pendaftar',
                'items' => [
                    ['field' => 'code'],
                    ['field' => 'registration_period_id'],
                    // ['field' => 'source_type'],
                    ['field' => 'qualified_status', 'options' => Pendaftar::QUALIFIED_STATUS],
                    ['field' => 'recommended_by'],
                    ['field' => 'utm_source', 'options' => Pendaftar::UTM_SOURCE],
                    ['field' => 'is_nim_imported', 'control' => 'switch'],
                ],
                'edit_url' => url()->current() . '/edit',
                'icon' => 'user',
            ],
            ['title' => 'Data Personal',
                'subtitle' => 'Informasi personal pendaftar',
                'items' => [
                    ['field' => 'nik'],
                    ['field' => 'name'],
                    ['field' => 'birth_place'],
                    ['field' => 'birth_date'],
                    ['field' => 'gender', 'control' => 'radio', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
                    ['field' => 'religion_id'],
                    ['field' => 'ethnic_id'],
                ],
                'icon' => 'user-plus',
            ],
            ['title' => 'Data Kontak dan Identitas',
                'subtitle' => 'Informasi kontak dan identitas pendaftar',
                'items' => [
                    ['field' => 'no_kk'],
                    ['field' => 'email'],
                    ['field' => 'phone_number'],
                    ['field' => 'npsn'],
                    ['field' => 'nokps'],
                    ['field' => 'passport'],
                ],
                'icon' => 'identification',
            ],
            ['title' => 'Data Alamat',
                'subtitle' => 'Informasi alamat pendaftar',
                'items' => [
                    ['field' => 'country_id'],
                    ['field' => 'province_id'],
                    ['field' => 'city_id'],
                    ['field' => 'district_id'],
                    ['field' => 'village_name'],
                    ['field' => 'dusun'],
                    ['field' => 'full_address'],
                    ['field' => 'rt'],
                    ['field' => 'rw'],
                    ['field' => 'postal_code'],
                ],
                'icon' => 'map-pin',
            ],
            ['title' => 'Data Pelengkap',
                'subtitle' => 'Informasi data pelengkap pendaftar',
                'items' => [
                    ['field' => 'job_id'],
                    ['field' => 'institution_name'],
                    ['field' => 'uniform_size'],
                    ['field' => 'weight', 'type' => 'number'],
                    ['field' => 'height', 'type' => 'number'],
                ],
            ],
        ];
    }
}
