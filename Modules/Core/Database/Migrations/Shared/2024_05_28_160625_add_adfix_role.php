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
        RoleInternal::updateOrCreate(
            ['kode_role' => RoleInternal::CODE_ADFIX],
            ['nama_role' => 'Admin Perbaikan Data', 'level_cp' => 2]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        RoleInternal::where('kode_role', RoleInternal::CODE_ADFIX)->delete();
    }
};
