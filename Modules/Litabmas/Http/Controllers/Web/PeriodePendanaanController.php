<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\PeriodePendanaanService;

class PeriodePendanaanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PeriodePendanaanService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $permissions = request()->permission;

        $header = [
            ['field' => 'tahun', 'label' => 'Periode Pendanaan', 'type' => 'number'],
            ['field' => 'tanggal_mulai', 'type' => 'date', 'defaultTypeDate' => true, 'component' => 'date', 'searchable' => false],
            ['field' => 'tanggal_akhir', 'type' => 'date', 'defaultTypeDate' => true, 'component' => 'date', 'searchable' => false],
            ['field' => 'maksimal_ketua_mendaftar', 'label' => 'Batas Ajuan <br/>(Ketua)', 'options' => PeriodePendanaan::MAX_KETUA_MENDAFTAR,
                // 'tooltip' => 'Menunjukkan batas maksimal jumlah pendaftaran yang dapat dilakukan seorang peneliti sebagai ketua dalam satu periode.'
            ],
            ['field' => 'maksimal_anggota_mendaftar', 'label' => 'Batas Ajuan <br/>(Anggota)', 'options' => PeriodePendanaan::MAX_ANGGOTA_MENDAFTAR,
                // 'tooltip' => 'Menunjukkan batas maksimal jumlah pendaftaran yang dapat dilakukan seorang peneliti sebagai anggota dalam satu periode.'
            ],
            ['field' => 'status_periode_pendanaan', 'component' => true, 'searchable' => false, 'sortable' => false,
                'hidden' => true, 'readonly' => true,
            ],
        ];

        $viewData = [
            'title' => 'Setting Periode Pendanaan',
            'showDeleteChecked' => false,
            'showNumber' => true,
            'staticAlert' => [
                'message' => 'Masukkan periode pendanaan berdasarkan penentuan rentang tanggal mulai dan akhir kegiatan pada masa periode pendanaan.'
            ],
            'canUpdate' => $permissions['put'],
        ];

        // default sorting by tahun
        if (empty($request->sort)) {
            $request->merge(['sort' => '1', 'sortDesc' => true]);
        }

        return WebController::index($this->service, $request, $header, viewData: $viewData, model: PeriodePendanaan::class, isReference: true);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store($this->service, $request, $this->defineFormFields(), PeriodePendanaan::class, isReference: true);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), PeriodePendanaan::class, isReference: true);
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
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            ['field' => 'tahun'],
            ['field' => 'tanggal_mulai', 'type' => 'date'],
            ['field' => 'tanggal_akhir', 'type' => 'date'],
            ['field' => 'maksimal_ketua_mendaftar', 'options' => PeriodePendanaan::MAX_KETUA_MENDAFTAR],
            ['field' => 'maksimal_anggota_mendaftar', 'options' => PeriodePendanaan::MAX_ANGGOTA_MENDAFTAR],
        ];
    }
}
