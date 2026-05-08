<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Check Exist Data table role_data
        $roleId = Role::where('kode_role', Role::ROLE_LITABMAS_ADMIN_LPPM)->first()->id;
        $resourceId = Resource::where('segmen_url', 'pengajuan-pendanaan')->first()->id;

        $permission = [
            'bisa_get' => true,
            'bisa_post' => true,
            'bisa_put' => true,
            'bisa_delete' => true,
        ];

        $roleData = DB::table('gate.role_akses')->where('id_role', $roleId)->where('id_resource', $resourceId)->first();

        // if not exist, insert data
        if (!$roleData) {
            DB::table('gate.role_akses')->insert([
                'id_role' => $roleId,
                'id_resource' => $resourceId,
                ...$permission
            ]);
        }
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
