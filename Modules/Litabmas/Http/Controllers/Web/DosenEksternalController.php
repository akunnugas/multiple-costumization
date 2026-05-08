<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Page;
use Modules\Core\Helpers\WebController;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\Litabmas\Services\DosenEksternalManagementService;

class DosenEksternalController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private DosenEksternalManagementService $service)
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
            ['field' => 'nip_nama', 'label' => 'Nama Peneliti'],
            ['field' => 'email_user', 'label' => 'Email'],
            ['field' => 'nama_pt', 'label' => 'Asal Institusi'],
            ['field' => 'nama_pengusul', 'Ketua Pengusul'],
            ['field' => 'status_usulan', 'component' => 'status_usulan', 'searchable' => false],
        ];

        $userRole = auth()->user()->kode_role;
        $roleDosen = [Role::ROLE_DOSEN_EKSTERNAL, Role::ROLE_DOSEN];
        $isPeneliti = in_array($userRole, $roleDosen);
        if ($isPeneliti) { // remove nama_pengusul
            unset($header[3]);
        }

        $viewData = [
            'showDeleteChecked' => false,
            'showNumber' => true,
            'title' => 'Pengajuan Akun Peneliti Eksternal',
        ];

        $filter = [
            'status_usulan' => [
                'options' => ['' => '-- Semua Status --'] + DosenEksternal::STATUS,
                'label' => 'Status Usulan', 'hideLabel' => true,
            ],
        ];

        return WebController::index(
            $this->service,
            $request,
            $header,
            filter: $filter,
            viewData: $viewData,
            model: DosenEksternal::class
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $viewData['title'] = 'Tambah Pengajuan Akun Peneliti Eksternal';

        return WebController::create($this->defineFormFields(), DosenEksternal::class, viewData: $viewData);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $userRole = auth()->user()->kode_role;
        $isDosen = $userRole === Role::ROLE_DOSEN;

        $successMessage = $isDosen
            ? 'Berhasil! Akun telah dibuat dan detail akun akan dikirimkan melalui email setelah akun disetujui oleh Admin.'
            : 'Berhasil! Akun telah dibuat dan detail akun telah dikirim melalui email';

        return WebController::store(
            $this->service,
            $request,
            $this->defineFormFields(),
            DosenEksternal::class,
            customMethod: 'usulkanDosenEksternal',
            successMessage: $successMessage
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $asalInstitusiOpt = PerguruanTinggi::options();

        $cards = [
            ['field' => 'nama_user', 'label' => 'Nama Peneliti'],
            ['field' => 'email_user', 'label' => 'Email', 'type' => 'email'],
            ['field' => 'nip', 'label' => 'NIP', 'type' => 'number', 'required' => true],
            ['field' => 'id_perguruan_tinggi_luar', 'label' => 'Asal Institusi', 'options' => $asalInstitusiOpt],
            ['field' => 'nama_pengusul', 'label' => 'Ketua Pengusul'],
            ['field' => 'status_usulan', 'component' => 'status_usulan'],
        ];

        $userRole = auth()->user()->kode_role;
        $roleDosen = [Role::ROLE_DOSEN_EKSTERNAL, Role::ROLE_DOSEN];
        $isPeneliti = in_array($userRole, $roleDosen);
        if ($isPeneliti) { // remove nama_pengusul
            unset($cards[4]);
        }

        return WebController::show($this->service, $id, $cards, DosenEksternal::class);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return WebController::edit($this->service, $id, $this->defineFormFields(true), DosenEksternal::class);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        return WebController::update($this->service, $id, $request, $this->defineFormFields(), DosenEksternal::class);
    }

    /**
     * Update status usulan, set role peneliti, dan kirim email invitation jika belumterdaftardi SSO.
     *
     * @param Request $request
     * @param $id
     * @return Renderable
     */
    public function approve(Request $request, $id)
    {
        WebController::validate($id);

        $successMessage = 'Berhasil menyetujui usulan dosen eksternal';
        return WebController::update(
            $this->service,
            $id,
            $request,
            fields: [],
            model: DosenEksternal::class,
            successMessage: $successMessage,
            customMethod: 'approveUsulanDosenEksternal',
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

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields($isEdit = false)
    {
        $userRole = auth()->user()->kode_role;
        $roleDosen = [Role::ROLE_DOSEN_EKSTERNAL, Role::ROLE_DOSEN];
        $canEdit = in_array($userRole, $roleDosen);
        $isRoleInternal = !empty(session('user.is_internal'));
        $disabled = (!$isRoleInternal && $isEdit && !$canEdit);

        $fields = [
            ['field' => 'nama_user', 'label' => 'Nama Peneliti', 'required' => true, 'disabled' => $disabled],
            ['field' => 'email_user', 'label' => 'Email', 'type' => 'email', 'required' => true, 'disabled' => $disabled],
            ['field' => 'nip', 'label' => 'NIP', 'type' => 'number', 'required' => true, 'disabled' => $disabled],
            ['field' => 'id_perguruan_tinggi_luar', 'label' => 'Asal Institusi', 'required' => false,
                'options' => PerguruanTinggi::options(), 'variant' => 'search', 'disabled' => $disabled
            ],
        ];

        // field custom untuk admin
        $isAdmin = !in_array($userRole, $roleDosen);
        if ($isAdmin) {
            $exceptIdDosenEksternals = ($isEdit) ? [Page::showURLInfo('id')] : [];
            $dosenOpt = DosenEksternal::optionWithBiodata(
                isDefaultOpt: true,
                showInternalExternal: true,
                exceptIdDosenEksternals: $exceptIdDosenEksternals
            );

            $fields[] = ['field' => 'id_biodata_pengusul', 'label' => 'Ketua Pengusul', 'options' => $dosenOpt,
                'variant' => 'search'
            ];
        }

        return $fields;
    }
}
