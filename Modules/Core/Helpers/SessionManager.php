<?php

namespace Modules\Core\Helpers;

use Modules\Core\Models\Shared\RoleInternal;

class SessionManager {
    public static function isInternalRole() {
        return in_array(auth()->user()?->kode_role, RoleInternal::getRoles());
    }

    public static function isSuperAdmin() {
        return auth()->user()?->kode_role === RoleInternal::CODE_SUPERADMIN;
    }

    public static function isAdminSupport() {
        return auth()->user()?->kode_role === RoleInternal::CODE_SUPPORT;
    }

    public static function isAdminAdfix() {
        return auth()->user()?->kode_role === RoleInternal::CODE_ADFIX;
    }
}