<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Helpers\Menu;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\BimbinganService;

class BimbinganController extends Controller
{
    protected string $kodeJenisPendanaan;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private BimbinganService $service, Request $request)
    {
        $kodeJenisPendanaan = $request->input('tab'); // didapat dari Menu::navTabs
        $allowedTab = JenisPendanaanEnum::CODES;
        if (empty($kodeJenisPendanaan) || !array_key_exists($kodeJenisPendanaan, $allowedTab)) { // klo kosong | tidak valid, set default
            $kodeJenisPendanaan = JenisPendanaanEnum::CODE_PENELITIAN;
        }
        $this->kodeJenisPendanaan = $kodeJenisPendanaan;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'tahun_periode_pendanaan', 'name' => 'id_periode_pendanaan', 'options' => PeriodePendanaan::class,
                'label' => 'Periode Pendanaan'],
            ['field' => 'judul_penelitian', 'label' => 'Judul Proposal'],
            ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
            ['field' => 'nama_sumber_pendanaan', 'label' => 'Sumber Pendanaan'],
            ['field' => 'nama_ketua', 'label' => 'Ketua Penelitian'],
            ['field' => 'id_dokumen_sk', 'label' => 'SK Pembimbing', 'component' => 'document_see_detail'],
            // ['field' => 'status_bimbingan_logbook', 'component' => true, 'label' => 'Status Review',
            //     'searchable' => false, 'sortable' => false],
            ['field' => 'action', 'component' => 'bimbingan']
        ];

        // set jenis pendanaan (default filter)
        $existingFilter = $request->input('filter', []);
        $request->merge([
            'filter' => array_merge($existingFilter, ['pp.kode_jenis_pendanaan' => $this->kodeJenisPendanaan])
        ]);

        // set default permission
        $permissions = request()->permission;
        $permissions['post'] = false;
        $permissions['put'] = false;
        $permissions['delete'] = false;
        $request->merge(['permission' => $permissions]);

        // filter
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

        // set view data
        $viewData = [
            'title' => 'Bimbingan PPM',
            'navTab' => Menu::navTabs('daftar-bimbingan'),
            'showNumber' => true,
            'showDeleteChecked' => false,
            'staticAlert' => [
                'message' => 'Silahkan Berikan bimbingan pada setiap proposal dengan memberikan masukan terhadap
                    aktivitas kegiatan yang telah dilakukan oleh peneliti.',
            ],
            'emptyState' => [
                'title' => 'Belum Ada Data Bimbingan',
                'subtitle' => 'Data bimbingan belum tersedia. Data akan tampil di sini saat Anda ditugaskan'
            ]
        ];

        // set active nav tab berdasarkan tab yang dipilih
        $activePath = 'bimbingan?tab=' . $this->kodeJenisPendanaan;
        $viewData['navTab']['activePath'] = $activePath;

        return WebController::index(
            $this->service,
            $request,
            $header,
            $filter,
            $viewData,
            PengajuanPendanaan::class,
        );
    }
}
