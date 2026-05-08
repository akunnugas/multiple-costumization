<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\WebController;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\PengajuanPendanaanService;

class PengajuanPendanaanController extends Controller
{
    protected $kodeJenisPendanaan = JenisPendanaanEnum::CODE_PENELITIAN;

    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private PengajuanPendanaanService $service, Request $request)
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
                'label' => 'Periode<br/>Pendanaan'
            ],
            ['field' => 'judul_penelitian', 'label' => 'Judul Penelitian', 'component' => 'judul_proposal'],
            ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
            ['field' => 'nama_ketua', 'label' => 'Ketua Penelitian'],
            ['field' => 'status', 'component' => 'status_agenda_kegiatan', 'searchable' => false, 'sortable' => false],
            ['field' => 'action', 'component' => 'pengajuan_pendanaan']
        ];

        // set jenis pendanaan (default filter)
        $existingFilter = $request->input('filter', []);
        $request->merge([
            'filter' => array_merge($existingFilter, ['pp.kode_jenis_pendanaan' => $this->kodeJenisPendanaan])
        ]);

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
                'title' => 'Belum Ada Proposal Penelitian',
                'subtitle' => 'Saat ini belum ada proposal penelitian yang tersedia. Proposal yang diajukan akan muncul di sini setelah proses pengajuan selesai'
            ]
        ];

        $userRole = auth()->user()?->kode_role;
        $isDosen = in_array($userRole, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

        if (!$isDosen) {
            $viewData['staticAlert'] = [
                'message' => 'Anda dapat menentukan seleksi pada halaman ini sesuai dengan agenda kegiatan dan melihat tanggal kegiatan tersebut'
            ];
        }

        return WebController::index(
            $this->service,
            $request,
            $header,
            $filter,
            $viewData,
            PengajuanPendanaan::class
        );
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
}
