<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\Shared\RoleInternal;
use Modules\Gate\Services\RoleManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create or update role
        RoleInternal::updateOrCreate(
            ['kode_role' => RoleInternal::CODE_SUPERADMIN],
            ['nama_role' => 'Superadmin', 'level_cp' => 1]
        );

        RoleInternal::updateOrCreate(
            ['kode_role' => RoleInternal::CODE_SUPPORT],
            ['nama_role' => 'Admin Support', 'level_cp' => 2]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        RoleInternal::where('kode_role', RoleInternal::CODE_SUPERADMIN)->delete();
        RoleInternal::where('kode_role', RoleInternal::CODE_SUPPORT)->delete();
    }
};
