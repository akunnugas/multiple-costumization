<?php

namespace Modules\HR\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\Agama;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\Biodata;
use Modules\Core\Services\PegawaiManagementService;

class EmployeeController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PegawaiManagementService $service)
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
            ['field' => 'nip'],
            ['field' => 'nama'],
            ['field' => 'nama_unit'],
            ['field' => 'homebase'],
            ['field' => 'status_pegawai'],
        ];

        $viewData['withSync'] = true;

        return WebController::index($this->service, $request, $header, viewData: $viewData);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $model = [Biodata::class, Pegawai::class];

        return WebController::create($this->defineFormFields(), $model);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $model = [Biodata::class, Pegawai::class];

        return WebController::store($this->service, $request, $this->defineFormFields(), $model);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $model = [Biodata::class, Pegawai::class];
        $cards = $this->defineFormFields();

        return WebController::show($this->service, $id, $cards, $model);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $model = [Biodata::class, Pegawai::class];
        $cards = $this->defineFormFields();

        return WebController::edit($this->service, $id, $cards, $model);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), Pegawai::class);
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
            [
                'title' => 'Data Personal',
                'subtitle' => 'Informasi personal pendaftar',
                'items' => [
                    ['field' => 'nik'],
                    ['field' => 'nama'],
                    ['field' => 'email'],
                    ['field' => 'gelar_depan'],
                    ['field' => 'gelar_belakang'],
                    ['field' => 'tempat_lahir'],
                    ['field' => 'tanggal_lahir'],
                    ['field' => 'jenis_kelamin', 'control' => 'radio', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
                    ['field' => 'id_agama'],
                    ['field' => 'id_suku'],
                    ['field' => 'id_negara'],
                    ['field' => 'id_provinsi'],
                    ['field' => 'id_kota'],
                    ['field' => 'id_kecamatan'],
                    ['field' => 'kode_pos'],
                    ['field' => 'alamat'],
                    ['field' => 'telepon'],
                ],
                'edit_url' => url()->current() . '/edit',
                'icon' => 'user',
            ],
            [
                'title' => 'Data Kepegawaian',
                'subtitle' => 'Informasi personal pendaftar',
                'items' => [
                    ['field' => 'nip'],
                    ['field' => 'id_unit_kerja'],
                    ['field' => 'id_status_pegawai'],
                    ['field' => 'id_hubungan_kerja'],
                    ['field' => 'nip_pns'],
                    ['field' => 'email_kampus'],
                    ['field' => 'akun_sidik_jari'],
                    ['field' => 'id_jabatan_struktural_atasan'],
                    ['field' => 'id_jabatan_akademik'],
                    ['field' => 'id_jabatan_fungsional'],
                ],
                'icon' => 'briefcase',
            ],
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
