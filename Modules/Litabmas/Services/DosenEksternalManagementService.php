<?php

namespace Modules\Litabmas\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\UnitKerjaCache;
use Modules\Core\Services\SsoService;
use Modules\Gate\Models\Api\UserSso;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Models\UserRole;
use Modules\Gate\Services\SiakadV1\UserRoleService;
use Modules\Gate\Services\SiakadV1\UserService;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PengajuanPendanaanReviewer;

class DosenEksternalManagementService
{
    /**
     * @var DosenEksternal
     */
    protected $model = DosenEksternal::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new DosenEksternal;
    }

    /**
     * Menampilkan list data
     *
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     *
     * @return mixed
     */
    public function index(int|null $page = null, int|null $perPage = null, array $order = [], array $filter = []): mixed
    {
        $table = $this->model->getTable();
        $sql = "select de.id, u.nama_user, u.email_user, CONCAT(de.nip, ' - ', u.nama_user) nip_nama, pt.nama_pt,
                peng.nama as nama_pengusul, de.status_usulan
            from $table de
            join core.biodata b on de.id_biodata = b.id AND b.waktu_dihapus is null
            join gate.user u on b.id_user = u.id AND u.waktu_dihapus is null
            left join core.biodata peng on de.id_biodata_pengusul = peng.id AND peng.waktu_dihapus is null
            left join core.perguruan_tinggi pt on pt.id = de.id_perguruan_tinggi_luar AND pt.waktu_dihapus is null";

        $defaultFilter = "de.waktu_dihapus is null";
        if (auth()->user()->kode_role === Role::ROLE_DOSEN) {
            $idBiodata = auth()->user()?->biodata?->id;
            $defaultFilter .= " AND de.id_biodata_pengusul = " . $idBiodata;
        }

        $fieldMap = [
            'nip_nama' => 'CONCAT(de.nip, \' - \', u.nama_user)',
            'nama_pt' => 'pt.nama_pt',
            'email_user' => 'u.email_user',
            'nama_pengusul' => 'peng.nama',
        ];

        [$sql, $bindings] = Pagination::buildQuery(
            query: $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            fieldMap: $fieldMap
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Menampilkan spesifik data berdasarkan id.
     *
     * @param int $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        $userRole = auth()->user()?->kode_role;
        $roleDosen = [Role::ROLE_DOSEN_EKSTERNAL, Role::ROLE_DOSEN];
        $isAdmin = !in_array($userRole, $roleDosen);

        $table = $this->model->getTable();
        $sql = "select de.id, u.nama_user, u.email_user, de.nip, CONCAT(de.nip, ' - ', u.nama_user) nip_nama,
                pt.nama_pt, de.status_usulan, de.id_perguruan_tinggi_luar,
                de.id_biodata_pengusul, peng.nama as nama_pengusul
            from $table de
            join core.biodata b on de.id_biodata = b.id AND b.waktu_dihapus is null
            join gate.user u on b.id_user = u.id AND u.waktu_dihapus is null
            left join core.biodata peng on de.id_biodata_pengusul = peng.id AND peng.waktu_dihapus is null
            left join core.perguruan_tinggi pt on pt.id = de.id_perguruan_tinggi_luar AND pt.waktu_dihapus is null";

        $defaultFilter = " where de.waktu_dihapus is null
            and de.id = :id_dosen_eksternal";
        $bindings['id_dosen_eksternal'] = $id;

        // cek scope jika bukan admin
        if (!$isAdmin) { // hanya yg pengusulnya dia
            $idBiodata = auth()->user()?->biodata?->id;
            $defaultFilter .= " and de.id_biodata_pengusul = :id_biodata_pengusul";
            $bindings['id_biodata_pengusul'] = $idBiodata;
        }

        // final sql query
        $sql .= $defaultFilter;
        $select = DB::select($sql, $bindings);

        if (!isset($select[0])) {
            throw new ModelNotFoundException();
        }

        return Collection::make($select[0]);
    }

    /**
     * Proses pemberian Role, Sync Siakad, dan Email Invitation.
     * Digunakan saat Approve atau saat Admin membuat user baru.
     */
    private function provisionAccount(DosenEksternal $dosenEksternal, User $user)
    {
        // Cek apakah akun sudah terdaftar di siakadv1
        $userSiakadV1 = (new UserService())->findByEmail($user->email_user);
        if ($userSiakadV1) {
            throw new Exception('Akun dengan email ' . $user->email_user . ' sudah terdaftar di Administrasi Aplikasi.');
        }

        // Ambil ID Unit Kerja Universitas
        $unitUniv = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        if (!$unitUniv) {
            throw new Exception('Data Unit Kerja Universitas tidak ditemukan.');
        }
        $idUnitKerjaUniv = $unitUniv->id;

        $unitCache = UnitKerjaCache::find($idUnitKerjaUniv);
        if (!$unitCache) {
            throw new Exception('Data Cache Unit Kerja Universitas belum disetting.');
        }
        // Support array/object
        $refKeyUnitKerjaUniv = $unitCache['ref_key_siakad'] ?? $unitCache->ref_key_siakad;

        // Set Role Dosen Eksternal
        // Jika user internal (Admin LPPM) yg approve, role ditempel ke Unit Kerja Univ
        $idUnitApproval = auth()->user()->id_unit;
        if (auth()->user()->is_internal) {
            $idUnitApproval = $idUnitKerjaUniv;
        }
        $this->setRoleDosenEksternal($user, $idUnitApproval);

        // Create user di table siakad v1
        $recUser = [
            'username' => $dosenEksternal->nip,
            'userdesc' => $user->nama_user,
            'isactive' => '1',
            'expired' => null,
            'email' => $user->email_user,
            'generatesandi' => '1'
        ];
        (new UserService())->store(data: $recUser);

        // Create user role di siakad v1
        // Ambil user yang baru dibuat untuk mendapatkan userid
        $userSiakadV1 = (new UserService())->findByEmail($user->email_user);
        if ($userSiakadV1) {
            $recUserRole = [
                'idrole' => 'doeks',
                'idsatker' => $refKeyUnitKerjaUniv,
                'userid' => $userSiakadV1->userid
            ];
            (new UserRoleService())->store(data: $recUserRole);
        }

        // Kirim Email Invitation SSO
        try {
            $sudahTerdaftarDiSso = UserSso::findUserByEmail($user->email_user);
            if (empty($sudahTerdaftarDiSso)) {
                // Redirect ke SIAKAD
                $client = request()->client;
                $urlMenuSiakad = $client['url_siakad_menu'] ?? env('URL_SIAKADV1_MENU', '#');
                (new SsoService())->sendEmailInvitationSso($user, false, $urlMenuSiakad);
            }
        } catch (Exception $e) {
            // Log error email tapi jangan throw exception agar transaksi DB (approve/create user) tetap commit
            // Log::error("Gagal mengirim email undangan SSO: " . $e->getMessage());
            // Optional: return warning message to controller if needed, but for now allow bypass
        }
    }

    /**
     * Usulkan/buat dosen eksternal baru.
     *
     * @param array $data
     * @return DosenEksternal|Error
     */
    public function usulkanDosenEksternal(array $data): DosenEksternal|Error
    {
        DB::beginTransaction();

        // Validasi jika sudah ada user sebelumnya berdasarkan email/NIP
        $result = $this->checkUniqueDosenEksternal($data['email_user'], $data['nip']);
        if (Error::isError($result)) {
            return $result;
        }

        try {
            // Buat atau Ambil User
            $user = User::firstOrCreate(['email_user' => $data['email_user']], [
                'nama_user' => $data['nama_user'],
                'email_user' => $data['email_user']
            ]);

            // Buat atau Ambil Biodata
            $biodata = Biodata::firstOrCreate(['id_user' => $user->id], [
                'nama' => $data['nama_user'],
                'email' => $data['email_user'],
            ]);

            // Cek Pengusul
            $userRole = auth()->user()->kode_role;
            $roleDosen = [Role::ROLE_DOSEN_EKSTERNAL, Role::ROLE_DOSEN];
            $isAdmin = !in_array($userRole, $roleDosen);

            $idPengusul = $isAdmin ?
                (!empty($data['id_biodata_pengusul']) ? Biodata::findOrFail($data['id_biodata_pengusul'])->id : null)
                : auth()->user()?->biodata?->id;

            // Siapkan Data Dosen Eksternal
            $record = [];
            $record['id_biodata'] = $biodata->id;
            $record['nip'] = $data['nip'];
            $record['id_biodata_pengusul'] = $idPengusul;
            // Jika admin yang buat, status langsung berhasil. Jika dosen, menunggu persetujuan.
            $record['status_usulan'] = $isAdmin ? DosenEksternal::STATUS_BERHASIL_DIBUAT : DosenEksternal::STATUS_MENUNGGU_PERSETUJUAN;

            if (!empty($data['id_perguruan_tinggi_luar'])) {
                $record['id_perguruan_tinggi_luar'] = PerguruanTinggi::find($data['id_perguruan_tinggi_luar'])->id;
            }

            // Simpan ke Database
            $dosenEksternal = $this->model->create($record);

            // Jika admin, jalankan provisi akun (Role, Sync, Email) sekarang
            if ($isAdmin) {
                $this->provisionAccount($dosenEksternal, $user);
            }

        } catch (Exception $e) {
            DB::rollBack();
            return new Error(message: $e->getMessage(), exception: $e);
        }

        DB::commit();

        return $dosenEksternal;
    }

    /**
     * Update spesifik data berdasarkan id.
     *
     * @param array $data
     * @param int $id
     * @return DosenEksternal|Error
     */
    public function update(array $data, int $id): DosenEksternal|Error
    {
        DB::beginTransaction();

        $userRole = auth()->user()?->kode_role;
        $roleDosen = [Role::ROLE_DOSEN_EKSTERNAL, Role::ROLE_DOSEN];
        $isAdmin = !in_array($userRole, $roleDosen);
        $isRoleInternal = !empty(session('user.is_internal'));

        try {
            $dosenEksternal = $this->model->findOrFail($id);

            // cek status_usulan
            if ($dosenEksternal->status_usulan === DosenEksternal::STATUS_BERHASIL_DIBUAT) {
                return new Error('Data tidak dapat diubah karena status usulan sudah berhasil dibuat.');
            }

            if (!$isRoleInternal && $isAdmin) {
                // admin hanya update id_biodata_pengusul
                $dosenEksternal->update([
                    'id_biodata_pengusul' => $data['id_biodata_pengusul']
                ]);
            } else {
                // validasi jika sudah ada user sebelumnya berdasarkan email
                $result = $this->checkUniqueDosenEksternal($data['email_user'], $data['nip'], $dosenEksternal->id);
                if (Error::isError($result)) {
                    return $result;
                }

                // update data dosen eksternal
                $dosenEksternal->update([
                    'id_perguruan_tinggi_luar' => $data['id_perguruan_tinggi_luar'],
                    'nip' => $data['nip'],
                ]);

                // update data biodata
                $biodata = Biodata::findOrFail($dosenEksternal->id_biodata);
                $biodata->update([
                    'nama' => $data['nama_user'],
                    'email' => $data['email_user'],
                ]);

                // update data user
                $user = User::findOrFail($biodata->id_user);
                $user->update([
                    'nama_user' => $data['nama_user'],
                    'email_user' => $data['email_user'],
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return new Error(message: $message ?? null, exception: $e);
        }

        DB::commit();

        return $dosenEksternal;
    }

    /**
     * Approve usulan dosen eksternal.
     * Update status usulan, set role peneliti, dan kirim email invitation jika belumterdaftardi SSO.
     *
     * @param array $data
     * @param int $id
     * @return DosenEksternal|Error
     */
    public function approveUsulanDosenEksternal(array $data, int $id): DosenEksternal|Error
    {
        DB::beginTransaction();

        try {
            // Update status usulan
            $dosenEksternal = $this->model->findOrFail($id);
            $dosenEksternal->update([
                'status_usulan' => DosenEksternal::STATUS_BERHASIL_DIBUAT,
            ]);

            $biodata = Biodata::findOrFail($dosenEksternal->id_biodata);
            $user = User::findOrFail($biodata->id_user);

            // Panggil method provisioning yang sudah dipisah
            $this->provisionAccount($dosenEksternal, $user);

        } catch (Exception $e) {
            DB::rollBack();
            return new Error(message: $e->getMessage(), exception: $e);
        }

        DB::commit();

        return $dosenEksternal;
    }

    /**
     * Hapus spesifik data berdasarkan id.
     *
     * @param int $id
     * @return null|Error
     */
    public function destroy(int $id): null|Error
    {
        DB::beginTransaction();

        try {
            $model = $this->model->findOrFail($id);

            // cek jika sudah dijadikan anggota penelitian, maka tidak bisa dihapus
            $biodata = Biodata::findOrFail($model->id_biodata);
            $anggotaPenelitianExists = PengajuanPendanaanAnggota::where('id_biodata', $biodata->id)->exists();
            if ($anggotaPenelitianExists) {
                return new Error('Data tidak dapat dihapus karena sudah dijadikan anggota penelitian.');
            }

            // cek jika sudah menjadi reviewer
            $apakahReviewerAdministrasi = PengajuanPendanaanReviewer::where('id_biodata', $biodata->id)->exists();
            if ($apakahReviewerAdministrasi) {
                return new Error('Data tidak dapat dihapus karena sudah dijadikan reviewer.');
            }

            $model->destroy($model->id);

            // hapus user role
            UserRole::where('id_user', $biodata->id_user)->delete();

            // hapus biodata
            Biodata::where('id', $model->id_biodata)->delete();

            // hapus user
            User::where('id', $biodata->id_user)->delete();

            // hapus user dan role di siakad v1
            $userSiakadV1 = (new UserService())->findByEmail($biodata->email);
            if (!empty($userSiakadV1)) {
                (new UserService())->destroy($userSiakadV1->userid);
            }
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        DB::commit();

        return null;
    }

    /*** --- [START] PRIVATE METHOD--- ***/
    /**
     * Set role peneliti.
     *
     * @param $user
     * @param $idUnitKerja
     * @return void
     */
    private function setRoleDosenEksternal($user, $idUnitKerja)
    {
        $role = Role::where('kode_role', Role::ROLE_DOSEN_EKSTERNAL)->first();

        UserRole::firstOrCreate([
            'id_user' => $user->id,
            'id_role' => $role->id,
            'id_unit_kerja' => $idUnitKerja,
        ]);
    }

    /**
     * cek unique berdasarkan email.
     * email dari gate.user jadi butuh custom.
     *
     * @param $email
     * @param $nip
     * @param null $idDosenEksternal
     * @return null|Error
     */
    private function checkUniqueDosenEksternal($email, $nip, $idDosenEksternal = null)
    {
        // cek unique user
        $exists = DosenEksternal::join('core.biodata as b', 'b.id', '=', 'litabmas.dosen_eksternal.id_biodata')
            ->join('gate.user as u', 'u.id', '=', 'b.id_user')
            ->where('u.email_user', $email)
            ->when($idDosenEksternal, function ($query) use ($idDosenEksternal) {
                return $query->where('dosen_eksternal.id', '<>', $idDosenEksternal);
            })
            ->select('dosen_eksternal.id')->exists();

        if ($exists) {
            return new Error(
                'Dosen eksternal dengan email ' . $email . ' sudah terdaftar.'
            );
        }

        // cek unique dosen eksternal by nip
        $exists = DosenEksternal::where('dosen_eksternal.nip', $nip)
            ->when($idDosenEksternal, function ($query) use ($idDosenEksternal) {
                return $query->where('dosen_eksternal.id', '<>', $idDosenEksternal);
            })
            ->select('dosen_eksternal.id')->exists();

        if ($exists) {
            return new Error(
                'Dosen eksternal dengan NIP ' . $nip . ' sudah terdaftar.'
            );
        }

        return null;
    }
    /*** --- [END] PRIVATE METHOD--- ***/

}