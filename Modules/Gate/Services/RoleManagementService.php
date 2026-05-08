<?php

namespace Modules\Gate\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Helpers\SyncSiakad;
use Modules\Core\Models\Shared\RoleInternal;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;
use Modules\Gate\Models\UserRole;

class RoleManagementService
{
    public $user;

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * @param int $page
     * @param int $perPage
     * @param array $order
     * @param array $filter
     * @return \Modules\Core\Helpers\Pagination
     */
    public function index($page = null, $perPage = null, $order = null, $filter = null)
    {
        $sql = "select id, kode_role, nama_role, apakah_statis, apakah_statis is true as _readonly
                from gate.role";
        $defaultFilter = "waktu_dihapus is null";

        [$sql, $bindings] = Pagination::buildQuery(
            $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
        );

        return Pagination::create($sql, $bindings, $page, $perPage);
    }

    /**
     * Store a newly created resource in storage.
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $model = new Role($data);
            $model->is_static = false;

            return $model->save();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $model = $this->findEditable($id);
            if (Error::isError($model)) {
                return $model;
            }

            $model->is_static = false;
            $model->update($data);

            return $model;
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $model = $this->findEditable($id);
            if (Error::isError($model)) {
                return $model;
            }

            $model->delete();
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Remove some resources from storage.
     * @param array $ids
     * @return mixed
     */
    public function destroySome($ids)
    {
        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $id) {
                    $model = $this->findEditable($id);
                    if (Error::isError($model)) {
                        throw new Exception;
                    }

                    $model->delete();
                }
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * Update the specified resource in storage.
     * @param int $id
     * @return mixed
     */
    private function findEditable($id)
    {
        $model = Role::findOrFail($id);
        if ($model->is_static) {
            return new Error;
        }

        return $model;
    }

    /**
     * Get data from SIAKAD V1 [gate.sc_role]
     *
     * @return array
     */
    public function getFromSiakadV1()
    {
        // FIXME: belum pasti tentang pindah extarnal role
        $connection = 'siakadv1';
        $internalRoles = RoleInternal::internallRolesV1();
        $query = "select * from gate.sc_role where idrole not in (" . "'" . implode("', '", $internalRoles) . "'" . ")";

        $result = DB::connection($connection)->select($query);
        $result = json_decode(json_encode($result), true);

        return $result;
    }

    public function getInternalFromSiakadV1()
    {
        // FIXME: belum pasti tentang pindah extarnal role
        $connection = 'siakadv1';
        $internalRoles = RoleInternal::internallRolesV1();
        $query = "select * from gate.sc_role where idrole in (" . "'" . implode("', '", $internalRoles) . "'" . ")";

        $result = DB::connection($connection)->select($query);
        $result = json_decode(json_encode($result), true);

        return $result;
    }

    /**
     * Sync data from SIAKAD V1 to SIAKAD V2
     *
     * return array
     */
    public function syncFromSiakadv1()
    {
        $roles = array_flip(Role::mapRoleInternalV1());

        // external role
        $dataSiakad = $this->getFromSiakadV1();
        $mapping = [];
        $mapping['idrole'] = ['column' => 'kode_role', 'options' => $roles, 'notnull' => true];
        $mapping['namarole'] = ['column' => 'nama_role'];
        $mapping['is_static'] = ['column' => 'apakah_statis', 'default' => false];
        // pk
        $pk = ['idrole'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, Role::class, $pk);

        return [$err, $msg];
    }

    /**
     * Sync data internal role from SIAKAD V1 to SIAKAD V2
     */
    public function syncInternalFromSiakadv1()
    {
        // internal role
        $dataSiakad = $this->getInternalFromSiakadV1();
        $roles = array_flip(RoleInternal::mapRoleInternalV1());
        $mapping = [];
        $mapping['idrole'] = ['column' => 'kode_role', 'options' => $roles, 'notnull' => true];
        $mapping['namarole'] = ['column' => 'nama_role'];
        $mapping['levelcp'] = ['column' => 'level_cp', 'default' => 0];
        // pk
        $pk = ['idrole'];

        list($err, $msg) = SyncSiakad::sync($mapping, $dataSiakad, RoleInternal::class, $pk);

        return [$err, $msg];
    }

    public function syncUserRoleFromSiakadV1($user, $kodeModul)
    {
        $err = false;
        $message = 'Data berhasil disinkronisasi';
        $isInternalUser = !empty($user?->id_role_internal) ? true : false;

        if ($isInternalUser) {
            // Jika user internal maka cek apakah punya user external
            $userExternal = User::where('id_user_sso', $user->id_user_sso)->first();
        }

        if (!empty($userExternal)) {
            $user = $userExternal;
        }

        $connection = 'siakadv1';
        $query =
            "SELECT
                ur.userid id_user,
                ur.idrole id_role,
                ur.idsatker id_unit_kerja
            FROM gate.sc_target t
            JOIN gate.sc_targetrole tr ON tr.idtarget = t.idtarget
            JOIN gate.sc_user u ON u.sso_account_id = :sso_account_id
            JOIN gate.sc_userrole ur ON tr.idrole = ur.idrole
                AND ur.userid = u.userid
            JOIN gate.sc_role r ON r.idrole = ur.idrole
            WHERE idmodul = :kode_modul
                AND (r.levelcp = 0 OR r.levelcp >= 3)";

        $result = DB::connection($connection)->select($query, ['sso_account_id' => $user->id_user_sso, 'kode_modul' => $kodeModul]);
        $result = json_decode(json_encode($result), true);

        $unitKerja = array_unique(array_column($result, 'id_unit_kerja'));
        $unitKerjaDefault = UnitKerja::whereIn('ref_key_siakad', $unitKerja)->get(['id', 'ref_key_siakad']);
        $unitKerjaSatker = UnitKerja::whereIn('ref_key_satker', $unitKerja)->get(['id', 'ref_key_satker']);
        $unitKerjaDefaultMap = array_column($unitKerjaDefault->toArray(), 'id', 'ref_key_siakad');
        $unitKerjaMapBySatker = array_column($unitKerjaSatker->toArray(), 'id', 'ref_key_satker');

        $internalRolesV1 = RoleInternal::mapRoleInternalV1();
        $externalRolesV1 = Role::mapRoleInternalV1();
        $roles = $internalRolesV1 + $externalRolesV1;

        $result = array_map(function ($item) use ($unitKerjaDefaultMap, $unitKerjaMapBySatker, $roles) {
            $item['id_unit_kerja'] = $unitKerjaDefaultMap[$item['id_unit_kerja']] ?? $unitKerjaMapBySatker[$item['id_unit_kerja']] ?? null;
            $item['id_role'] = $roles[$item['id_role']] ?? null;
            return $item;
        }, $result);

        // Filter data yang id_rolenya tidak kosong
        $result = array_filter($result, function ($item) {
            return !empty($item['id_role']) && !empty($item['id_unit_kerja']);
        });

        $internalRoles = RoleInternal::whereIn('kode_role', array_column($result, 'id_role'))->pluck('id', 'kode_role')->toArray();
        $externalRoles = Role::whereIn('kode_role', array_column($result, 'id_role'))->pluck('id', 'kode_role')->toArray();
        $rolesV2 = $internalRoles + $externalRoles;


        // Jika mempunya role eksternal tetapi tidak ada user eksternal, maka buat user eksternal
        if ($isInternalUser && empty($userExternal)) {
            $user = User::firstOrCreate(
                Arr::only($user->toArray(), ['id_user_sso', 'nama_user', 'email_user']),
                Arr::only($user->toArray(), ['id_user_sso', 'nama_user', 'email_user', 'telepon_user', 'waktu_verifikasi_email'])
            );
        }

        $result = array_map(function ($item) use ($user, $rolesV2) {
            $item['id_user'] = $user->id;
            $item['id_role'] = $rolesV2[$item['id_role']] ?? null;
            return $item;
        }, $result);

        // Filter data yang id_rolenya v2 tidak kosong
        $result = array_values(array_filter($result, function ($item) {
            return !empty($item['id_role']);
        }));

        DB::beginTransaction();

        foreach ($result as $item) {
            try {
                UserRole::updateOrCreate(
                    Arr::only($item, ['id_user', 'id_role']),
                    $item
                );
            } catch (\Throwable $th) {
                $err = true;
                $message = $th->getMessage();
            }
        }

        if ($err) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        return [$err, $message];
    }
}
