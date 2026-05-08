<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\JenisAktivitas;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\PenilaianReviewerManagementService;

class PenilaianReviewerController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PenilaianReviewerManagementService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = $this->defineFormFields();
        $permissions = request()->permission;
        $permissions['post'] = false;
        $permissions['put'] = false;
        $permissions['delete'] = false;
        $request->merge(['permission' => $permissions]);

        $filter = [
            'id_periode_pendanaan' => [
                'options' => ['' => '-- Semua Periode Pendanaan --'] + PeriodePendanaan::options(),
                'hideLabel' => true,
            ],
            'kode_jenis_pendanaan' => [
                'options' => ['' => '-- Semua Jenis Pendanaan --'] + JenisPendanaanEnum::CODES,
                'hideLabel' => true,
            ],
        ];

        $viewData = [
            'showDeleteChecked' => false,
            'showNumber' => true,
            'title' => 'Penilaian PPM',
            'emptyState' => [
                'title' => 'Belum Ada Data Proposal Penelitian & Pengabdian Masyarakat',
                'subtitle' => 'Data proposal Peneltian & Pengabdian Masyarakat akan muncul setelah Anda ditunjuk sebagai reviewer.',
            ]
        ];

        return WebController::index($this->service, $request, $header, $filter, viewData: $viewData);
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), JenisAktivitas::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), JenisAktivitas::class, isReference: true);
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
            ['field' => 'nama_periode_pendanaan', 'sortable' => true, 'label' => 'Periode Pendanaan'],
            ['field' => 'judul_penelitian', 'sortable' => true, 'label' => 'Judul Proposal'],
            ['field' => 'nama_klaster', 'sortable' => true, 'label' => 'Klaster Pendanaan'],
            ['field' => 'kode_jenis_pendanaan', 'sortable' => true, 'label' => 'Jenis Pendanaan', 'searchable' => false],
            ['field' => 'bertugas_sebagai', 'component' => 'bertugas_sebagai_reviewer', 'sortable' => false],
            ['field' => 'status_penilaian', 'component' => 'status_penilaian_reviewer', 'sortable' => false, 'searchable' => false],
            ['field' => 'action', 'component' => 'penilaian_reviewer', 'sortable' => false, 'searchable' => false]
        ];
    }
}
