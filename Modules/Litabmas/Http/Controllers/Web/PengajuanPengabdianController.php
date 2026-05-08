<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Gate\Models\Role;
use Illuminate\Routing\Controller;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Services\PengajuanPendanaanService;


class PengajuanPengabdianController extends Controller
{
    protected $kodeJenisPendanaan = JenisPendanaanEnum::CODE_PENGABDIAN;
    protected $service;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $this->service = new PengajuanPendanaanService();
        $permissions = request()->permission;

        // dosen eksternal tidak bisa hapus ataupun mengajukan ujian
        if (auth()->user()?->kode_role === Role::ROLE_DOSEN_EKSTERNAL) {
            $permissions['post'] = false;
            $permissions['delete'] = false;

            $request->merge(['permission' => $permissions]);
        }

        // role internal
        if (!empty(session('user.is_internal'))) {
            $permissions['post'] = false;
            $request->merge(['permission' => $permissions]);
        }

        $header = [
            [
                'field' => 'tahun_periode_pendanaan',
                'name' => 'id_periode_pendanaan',
                'options' => PeriodePendanaan::class,
                'label' => 'Periode Pendanaan'
            ],
            ['field' => 'judul_penelitian', 'label' => 'Judul Pengabdian', 'component' => 'judul_proposal'],
            ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
            ['field' => 'nama_ketua', 'label' => 'Ketua Pengabdian'],
            ['field' => 'status', 'component' => 'status_agenda_kegiatan', 'searchable' => false, 'sortable' => false],
            ['field' => 'action', 'component' => 'pengajuan_pendanaan']
        ];

        // set jenis pendanaan (default filter)

        $filter = [
            'id_periode_pendanaan' => [
                'options' => ['' => '-- Semua Periode Pendanaan --'] + PeriodePendanaan::options(),
                'hideLabel' => true,
            ],
            'pp.id_klaster_pendanaan' => [
                'options' => ['' => '-- Semua Klaster Pendanaan --'] + KlasterPendanaan::options(),
                'hideLabel' => true,
            ]
        ];

        // set view data
        $viewData = [
            'showDeleteChecked' => false,
            'createLabel' => 'Pilih Klaster',
            'customCreateUrl' => route('litabmas.pengumuman-klaster.index') . '?jenis_pendanaan=' . $this->kodeJenisPendanaan,
            'emptyState' => [
                'title' => 'Belum Ada Proposal Pengabdian',
                'subtitle' => 'Saat ini belum ada proposal pengabdian yang tersedia. Proposal yang diajukan akan muncul di sini setelah proses pengajuan selesai'
            ]
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            $filter,
            $viewData,
            PengajuanPendanaan::class,
            customMethodParams: [$this->kodeJenisPendanaan]
        );
    }

    public function destroy($id)
    {
        $this->service = new PengajuanPendanaanService();
        return WebController::destroy($this->service, $id);
    }
}
