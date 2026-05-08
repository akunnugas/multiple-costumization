<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Shared\RoleInternal;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create or update role
        RoleInternal::updateOrCreate(
            ['kode_role' => RoleInternal::CODE_SUPERADMIN],
            [
                'kode_role' => RoleInternal::ROLE_INTERNAL[RoleInternal::CODE_SUPERADMIN]
            ]
        );

        RoleInternal::updateOrCreate(
            ['kode_role' => RoleInternal::CODE_SUPPORT],
            [
                'kode_role' => RoleInternal::ROLE_INTERNAL[RoleInternal::CODE_SUPPORT]
            ]
        );

        RoleInternal::updateOrCreate(
            ['kode_role' => RoleInternal::CODE_ADFIX],
            [
                'kode_role' => RoleInternal::ROLE_INTERNAL[RoleInternal::CODE_ADFIX]
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
