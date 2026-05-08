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
        DB::beginTransaction();

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

        //If not exist, insert data
        if (!$roleData) {
            try{
                DB::table('gate.role_akses')->updateOrInsert(
                    ['id_role' => $roleId, 'id_resource' => $resourceId],
                    ['id_role' => $roleId, 'id_resource' => $resourceId, ...$permission]
                );
            } catch (\Exception $e) {
                DB::rollBack();
            }

            DB::commit();
        }
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        try {
            // Check if the role and resource exist to ensure safe deletion
            $roleId = Role::where('kode_role', Role::ROLE_LITABMAS_ADMIN_LPPM)->first()->id;
            $resourceId = Resource::where('segmen_url', 'pengajuan-pendanaan')->first()->id;

            // Delete the role_akses entry if it exists
            DB::table('gate.role_akses')
                ->where('id_role', $roleId)
                ->where('id_resource', $resourceId)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
};
