<?php

namespace Modules\Gate\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error;
use Modules\Core\Models\Shared\UserInternal;
use Modules\Gate\Models\ModulCache;
use Modules\Gate\Models\RoleAksesCache;

class AuthorizationService
{
    /**
     * Get authorized modules with user roles.
     *
     * @param object $user
     * @return array
     */
    public static function showAuthorizedModules($user = null)
    {
        if (empty($user)) {
            $user = Auth::user();
        }
        if (empty($user)) {
            return new Error('User tidak ditemukan');
        }

        // daftar modul
        $modules = ModulCache::get();

        // user internal berhak akses semua modul
        $email = $user->email_user;
        $internalUser = UserInternal::where('email_user', $email)->first();
        if (!empty($internalUser)) {
            foreach ($modules as $moduleCode => $module) {
                $modules[$moduleCode]['role'] = [
                    [
                        'id' => $internalUser->role->id,
                        'nama_role' => $internalUser->role->nama_role,
                        'kode_role' => $internalUser->role->kode_role,
                        'id_unit' => 0,
                        'nama_unit' => "SEVIMA",
                    ]
                ];
            }
        }

        $moduleCodes = [];
        foreach ($modules as $module) {
            $moduleCodes[$module['id']] = $module['kode_modul'];
        }

        // mendapatkan user role berdasarkan email karena user bisa memiliki role internal dan external
        $sql = "select ur.id_role, rl.nama_role, rl.kode_role, ur.id_unit_kerja, u.nama_unit
                from gate.user_role ur
                join gate.user us on us.id = ur.id_user and us.waktu_dihapus is null
                join gate.role rl on rl.id = ur.id_role and rl.waktu_dihapus is null
                join core.unit_kerja u on u.id = ur.id_unit_kerja and u.waktu_dihapus is null
                where us.email_user = ? and ur.waktu_dihapus is null";
        $userRoles = DB::select($sql, [$user->email_user]);

        $permissions = [];
        foreach ($userRoles as $userRole) {
            $permissions[$userRole->id_role] ??= RoleAksesCache::find($userRole->id_role);
            $permission = $permissions[$userRole->id_role];

            if (empty($permission)) {
                continue;
            }

            foreach ($permission as $moduleId => $modulePermission) {
                $moduleCode = $moduleCodes[$moduleId];

                if (empty($modules[$moduleCode]['role'])) {
                    $modules[$moduleCode]['role'] = [];
                }

                $data = [
                    'id' => $userRole->id_role,
                    'nama_role' => $userRole->nama_role,
                    'kode_role' => $userRole->kode_role,
                    'id_unit' => $userRole->id_unit_kerja,
                    'nama_unit' => $userRole->nama_unit
                ];

                $modules[$moduleCode]['role'][] = $data;
            }
        }

        // hanya yang memiliki roles yang sesuai dengan $user->role_id
        $authorizedModules = [];
        foreach ($modules as $module) {
            if (empty($module['role'])) {
                continue;
            }

            foreach ($module['role'] as $role) {
                if ($role['id'] == $user->id_role) {
                    $authorizedModules[$module['kode_modul']] = $module;
                    break;
                }
            }
        }

        return $authorizedModules;
    }

    /**
     * Get role permissions.
     *
     * @return mixed
     */
    public static function showRolePermissions()
    {
        $user = Auth::user();
        if (empty($user)) {
            return new Error('User belum login');
        }

        if ($user->is_internal) {
            return true;
        }

        return RoleAksesCache::find($user->id_role)[$user->id_modul] ?? null;
    }
}
