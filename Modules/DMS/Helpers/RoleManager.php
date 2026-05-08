<?php

namespace Modules\DMS\Helpers;

use Auth;
use Modules\Core\Models\Shared\RoleInternal;
use Modules\Core\Models\Shared\RoleInternalCache;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\RoleCache;

class RoleManager {
    const ROOT_ROLES = [
        RoleInternal::CODE_V2_SUPERADMIN,
        Role::ROLE_ADMINPT,
        Role::ROLE_ADMINDMS
    ];

    public static function getCurrentUserRole() {
        $user = Auth::user();

        if ($user->is_internal) {
            return RoleInternalCache::find($user->id_role);
        }

        return RoleCache::find($user->id_role);
    }

    public static function isRootRole() {
        return in_array(self::getCurrentUserRole()['kode_role'] ?? null, self::ROOT_ROLES);
    }
}
