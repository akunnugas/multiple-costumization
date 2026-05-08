<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\PendanaanKegiatanService;

class PendanaanKegiatanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private PendanaanKegiatanService $service,
    ) {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        // overide permissions to false, except get
        $permissions = request()->permission;
        $permissions['post'] = false;
        $permissions['put'] = false;
        $permissions['delete'] = false;
        $request->merge(['permission' => $permissions]);

        $headers = [
            ['field' => 'periode', 'label' => 'Periode Pendanaan'],
            ['field' => 'nama_sumber_pendanaan', 'label' => 'Sumber Pendanaan'],
            ['field' => 'pengelola_bantuan', 'label' => 'Pengelola Pendanaan'],
            ['field' => 'total_pendanaan', 'label' => 'Total Pendanaan', 'component' => 'format_currency', 'styleAlign' => 'right', 'searchable' => false],
            ['field' => 'dana_diberikan', 'label' => 'Dana Diberikan', 'component' => 'format_currency', 'styleAlign' => 'right', 'searchable' => false],
            ['field' => 'dana_tersisa', 'label' => 'Dana Tersisa', 'component' => 'format_currency', 'styleAlign' => 'right', 'searchable' => false],
            ['field' => 'total_proposal', 'label' => 'Proposal Diterima', 'styleAlign' => 'right', 'searchable' => false],
            ['field' => 'action', 'component' => 'pendanaan_kegiatan']
        ];

        $filter = [
            'id_periode_pendanaan' => [
                'options' => ['' => '-- Semua Periode Pendanaan --'] + PeriodePendanaan::options(),
                'hideLabel' => true,
            ],
            'id_sumber_pendanaan' => [
                'options' => ['' => '-- Semua Sumber Pendanaan --'] + SumberPendanaan::options(),
                'hideLabel' => true,
            ],
        ];

        $viewData = [
            'title' => 'Monitoring Pendanaan',
            'showDeleteChecked' => false,
            'showNumber' => true,
            'canCreate' => false,
            'emptyState' => [
                'title' => 'Belum Ada Proposal yang Didanai',
                'subtitle' => 'Saat ini belum ada proposal yang telah didanai. Data proposal yang telah didanai akan muncul di sini setelah pendanaan dilakukan'
            ]
        ];

        return WebController::index($this->service, $request, $headers, filter: $filter, viewData: $viewData);
    }
}
