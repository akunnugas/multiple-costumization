<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        $resource = Resource::where('id_modul', $idModul)->where('segmen_url', 'bidang-ilmu')->first();

        if ($resource) {
            $role = Role::where('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->first();

            // update permission
            try {
                DB::table('gate.role_akses')
                    ->where('id_role', $role->id)
                    ->where('id_resource', $resource->id)
                    ->update([
                        'bisa_post' => true,
                    ]);
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        $resource = Resource::where('id_modul', $idModul)->where('segmen_url', 'bidang-ilmu')->first();

        if ($resource) {
            $role = Role::where('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->first();

            // update permission
            try {
                DB::table('gate.role_akses')
                    ->where('id_role', $role->id)
                    ->where('id_resource', $resource->id)
                    ->update([
                        'bisa_post' => false,
                    ]);
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
    }
};
