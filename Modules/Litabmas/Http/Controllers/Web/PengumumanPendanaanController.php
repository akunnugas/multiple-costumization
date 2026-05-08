<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PengumumanPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\PengumumanPendanaanManagementService;

class PengumumanPendanaanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private PengumumanPendanaanManagementService $service,
        private PengumumanPendanaan $model
    ) {}

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        // overide permissions to false, except get
        $permissions = request()->permission;
        $permissions['put'] = false;
        $request->merge(['permission' => $permissions]);

        $isDosen = in_array(auth()->user()?->kode_role, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

        $header = [
            ['field' => 'nama_periode_pendanaan', 'label' => 'Periode Pengumuman'],
            ['field' => 'judul', 'label' => 'Judul Pengumuman'],
            ['field' => 'waktu_dipublikasi', 'searchable' => false, 'label' => 'Tanggal Pengumuman', 'component' => 'tanggal_pengumuman'],
            ['field' => 'id_dokumen_lampiran', 'searchable' => false, 'label' => 'Dokumen', 'component' => 'document_see_detail'],
        ];

        if (!$isDosen) {
            $header[] = ['field' => 'status_pengumuman', 'label' => 'Status Pengumuman', 'component' => true];
        }

        $filter = [
            'status_pengumuman' => [
                'options' => ['' => '-- Semua Status Pengumuman --'] + PengumumanPendanaan::STATUS_OPTIONS,
                'hideLabel' => true,
            ],
        ];

        $viewData = [
            'showDeleteChecked' => false,
            'showNumber' => true,
            'createLabel' => 'Buat Pengumuman'
        ];

        if ($isDosen) {
            $viewData['emptyState'] = [
                'title' => 'Belum Ada Pengumuman',
                'subtitle' => 'Saat ini tidak ada pengumuman yang tersedia. Pengumuman terbaru akan muncul di sini begitu diunggah'
            ];
        }

        return WebController::index(
            $this->service,
            $request,
            $header,
            $filter,
            $viewData,
            model: $this->model::class
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $fields = $this->defineFormFields();
        $periodeAktif = PeriodePendanaan::periodeAktif();
        if (!empty($periodeAktif)) {
            $fields[0]['options'][$periodeAktif->id] = $periodeAktif->tahun . ' (Periode Aktif)';
        }
        return WebController::create($fields, PengumumanPendanaan::class);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        return WebController::store(
            $this->service,
            $request,
            fields: $this->defineFormFields(),
            model: $this->model::class
        );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request, $id)
    {
        $fields = $this->defineFormFields();
        $periodeAktif = PeriodePendanaan::periodeAktif();
        if (!empty($periodeAktif)) {
            $fields[0]['options'][$periodeAktif->id] = $periodeAktif->tahun . ' (Periode Aktif)';
        }

        return WebController::edit($this->service, $id, $fields, $this->model::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request, $id)
    {
        return WebController::update(
            $this->service,
            $id,
            $request,
            fields: $this->defineFormFields(),
            model: $this->model::class
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id)
    {
        $cards = $this->defineFormFields();

        $cards[] = ['field' => 'tanggal_dipublikasi', 'label' => 'Tanggal Pengumuman'];

        $isDosen = in_array(auth()->user()?->kode_role, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

        if ($isDosen) {
            foreach ($cards as $key => $card) {
                if ($card['field'] === 'status_pengumuman') {
                    unset($cards[$key]);
                }
            }
        }

        $viewData['isDetailV2'] = true;

        return WebController::show($this->service, $id, $cards, $this->model::class, viewData: $viewData);
    }

    /**
     * Proses publikasi pengumuman.
     *
     * @param Request $request
     * @param $id
     * @return Renderable
     */
    public function publikasikanPengumuman(Request $request, $id)
    {
        $successMessage = 'Berhasil mempublikasikan pengumuman pendanaan.';
        return WebController::update(
            $this->service,
            $id,
            $request,
            fields: [],
            model: $this->model::class,
            successMessage: $successMessage,
            customMethod: 'publikasikanPengumuman',
        );
    }

    /**
     * Proses membatalkan publikasi pengumuman.
     *
     * @param Request $request
     * @param $id
     * @return Renderable
     */
    public function batalkanPublikasiPengumuman(Request $request, $id)
    {
        $successMessage = 'Berhasil membatalkan publikasi pengumuman pendanaan.';
        return WebController::update(
            $this->service,
            $id,
            $request,
            fields: [],
            model: $this->model::class,
            successMessage: $successMessage,
            customMethod: 'batalkanPublikasiPengumuman',
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $successMessage = 'Berhasil menghapus pengumuman pendanaan.';
        return WebController::destroy($this->service, $id, successMessage: $successMessage);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        return [
            [
                'field' => 'tahun_periode_pendanaan',
                'name' => 'id_periode_pendanaan',
                'options' => PeriodePendanaan::options(),
                'label' => 'Periode Pengumuman',
                'required' => true
            ],
            ['field' => 'judul', 'label' => 'Judul Pengumuman', 'required' => true],
            ['field' => 'informasi', 'required' => true, 'control' => 'wysiwyg', 'type' => 'breakline'],
            ['field' => 'id_dokumen_lampiran', 'label' => 'Dokumen', 'component' => 'file'],
            [
                'field' => 'status_pengumuman',
                'label' => 'Status Pengumuman',
                'options' => PengumumanPendanaan::STATUS_OPTIONS,
                'default' => PengumumanPendanaan::STATUS_DRAFT,
                'badge' => PengumumanPendanaan::STATUS_BADGE,
            ],
        ];
    }
}
