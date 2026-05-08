<?php

namespace Modules\SPMI\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\MenuHelper;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\Modul;
use Modules\SPMI\Helpers\Menu;

class UnitKerjaController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private UnitKerjaManagementService $service) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'kode_unit'],
            ['field' => 'nama_unit'],
            ['field' => 'parent_unit'],
            ['field' => 'apakah_aktif', 'component' => 'apakah_aktif_atau_tidak_aktif', 'searchable' => false, 'label' => '	Status Keaktifan Unit Kerja'],
            ['field' => 'jenis_unit', 'component' => 'jenis_unit', 'searchable' => false],
            ['field' => 'action', 'component' => "unit_kerja_action", 'searchable' => false],
        ];

        $viewData['sidebar'] = Menu::masterSidebar('organization');
        $viewData['withSync'] = true;

        $isActiveHR = false;
        $checkHR = DB::connection('siakadv1')->select("select isaktif from gate.sc_modul where idmodul = 'hr'");
        if ($checkHR) {
            $checkHR = reset($checkHR);
            $isActiveHR = $checkHR->isaktif == 1 ? true : false;
        }
        $viewData['isActiveHR'] = $isActiveHR;

        // saat ini hak akses delete tidak dapat digunakan, karena sync ke akademik
        $request->merge(['permission' => array_merge($request->permission, ['delete' => true])]);

        return WebController::index($this->service, $request, $header, [], $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.organization.unit-kerja');
        return WebController::create($this->defineUnitNonProdiFields(), UnitKerja::class, $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store(
            service: $this->service,
            request: $request,
            fields: $this->defineUnitNonProdiFields(),
            model: UnitKerja::class,
            customMethod: 'storeUnitNonProdi'
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $unit = UnitKerja::findOrFail($id);
        $cards = $this->defineFormFields();
        if ($unit->jenis_unit == UnitKerja::UNIT_NON_PRODI) {
            $cards = $this->defineUnitNonProdiFields();
        }

        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.organization.unit-kerja');
        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, UnitKerja::class, $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $unit = UnitKerja::findOrFail($id);
        $cards = $this->defineFormFields();
        if ($unit->jenis_unit == UnitKerja::UNIT_NON_PRODI) {
            $cards = $this->defineUnitNonProdiFields();
        }

        $viewData['lastBreadcrumb'] = MenuHelper::getItem(Menu::class, 'masterSidebar.organization.unit-kerja');
        return WebController::edit($this->service, $id, $cards, UnitKerja::class, $viewData);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), UnitKerja::class);
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
        $onlyAllowNow = LembagaAkreditasi::pluck('nama_lembaga', 'id')->toArray();

        return [
            ['field' => 'kode_unit'],
            ['field' => 'nama_unit'],
            ['field' => 'jenis_unit', 'disabled' => true],
            ['field' => 'id_parent', 'options' => UnitKerja::whereIn('jenis_unit', [UnitKerja::FACULTY, UnitKerja::UNIVERSITY])->pluck('nama_unit', 'id')->toArray()],
            ['field' => 'id_lembaga_akreditasi', 'label' => 'Standar IKU', 'options' => $onlyAllowNow],
            ['field' => 'kebutuhan_lulusan'],
            ['field' => 'kelompok_prodi'],
            ['field' => 'tanggal_berdiri', 'type' => 'date'],
            ['field' => 'apakah_aktif', 'label' => 'Apakah Aktif', 'control' => 'radio', 'options' => [1 => 'Aktif', 0 => 'Tidak Aktif']],
            ['field' => 'id_jenjang_pendidikan'],
            ['field' => 'pimpinan', 'label' => 'Ketua Prodi'],
        ];
    }

    private function defineUnitNonProdiFields()
    {
        return [
            ['field' => 'kode_unit'],
            ['field' => 'nama_unit'],
            ['field' => 'id_parent', 'options' => UnitKerja::whereIn('jenis_unit', [UnitKerja::FACULTY, UnitKerja::UNIVERSITY])->pluck('nama_unit', 'id')->toArray(), 'required' => true],
            ['field' => 'apakah_aktif', 'label' => 'Apakah Aktif', 'control' => 'radio', 'options' => [1 => 'Aktif', 0 => 'Tidak Aktif']],
        ];
    }

    /**
     * Sync From Siakad v1
     *
     * @return Renderable
     */
    public function sync()
    {
        return WebController::sync($this->service);
    }
}
