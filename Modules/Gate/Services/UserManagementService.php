<?php

namespace Modules\Gate\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Pagination;
use Modules\Core\Models\Shared\UserInternal;
use Modules\Gate\Models\User;
use Modules\Gate\Models\UserRole;

class UserManagementService
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
        $sql = "select u.id, u.nama_user, u.email_user, u.waktu_verifikasi_email is not null as email_terverifikasi,
                string_agg(r.nama_role || ' - ' || o.nama_unit, '<br />' order by r.kode_role, o.info_left) as role
                from gate.user u
                left join gate.user_role ur on ur.id_user = u.id and ur.waktu_dihapus is null
                left join gate.role r on r.id = ur.id_role and r.waktu_dihapus is null
                left join core.unit_kerja o on o.id = ur.id_unit_kerja and o.waktu_dihapus is null";
        $defaultFilter = "u.waktu_dihapus is null";
        $groupBy = "u.id";

        [$sql, $bindings] = Pagination::buildQuery(
            $sql,
            order: $order,
            filter: $filter,
            defaultFilter: $defaultFilter,
            groupBy: $groupBy,
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
            return User::create($data);
        } catch (Exception $e) {
            return new Error(exception: $e);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function show($id)
    {
        try {
            return User::findOrFail($id);
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
            $model = User::findOrFail($id);
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
            DB::transaction(function () use ($id) {
                UserRole::destroy(
                    UserRole::where('id_user', $id)->pluck('id')
                );
                User::destroy([$id]);
            });
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
                UserRole::destroy(
                    UserRole::whereIn('id_user', $ids)->pluck('id')
                );
                User::destroy($ids);
            });
        } catch (Exception $e) {
            return new Error(exception: $e);
        }

        return null;
    }

    /**
     * get user role
     *
     * @return array
     */
    public function getUserRole()
    {
        $user = Auth::user();
        // get email
        $email = $user->email;

        // get user role by Email
        $userInternalRole = UserInternal::where('email_user', $email)->first()?->role;
        $userExternalRole = User::where('email_user', $email)->first()?->roles;

        // combine user role external and internal
        $userRole = [];
        if ($userInternalRole) {
            $userRole[$userInternalRole->kode_role] = $userInternalRole->nama_role;
        }
        if ($userExternalRole) {
            foreach ($userExternalRole as $role) {
                $userRole[$role->kode_role] = $role->nama_role;
            }
        }

        return $userRole;
    }
}
