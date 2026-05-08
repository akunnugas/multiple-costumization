<?php

namespace Modules\SPMI\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Extensions\SiakadUser;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\DokumenHasilAudit;

class DokumenHasilAuditPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(SiakadUser $user, DokumenHasilAudit $dokumenHasilAudit)
    {
        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit)) {
            return $this->authorizeUnitKerja($userUnit, $dokumenHasilAudit);
        }

        return false;
    }

    public function update(SiakadUser $user, DokumenHasilAudit $dokumenHasilAudit)
    {
        $userUnit = session()->get('user.unit_kerja');
        if (!empty($userUnit)) {
            return $this->authorizeUnitKerja($userUnit, $dokumenHasilAudit);
        }

        return false;
    }

    protected function authorizeUnitKerja(mixed $userUnit, DokumenHasilAudit $dokumenHasilAudit)
    {
        $userUnit = UnitKerja::where('info_left', '>=', $userUnit->info_left)
            ->where('info_right', '<=', $userUnit->info_right)
            ->where('id', $dokumenHasilAudit->id_unit)
            ->exists();

        return $userUnit;
    }
}
