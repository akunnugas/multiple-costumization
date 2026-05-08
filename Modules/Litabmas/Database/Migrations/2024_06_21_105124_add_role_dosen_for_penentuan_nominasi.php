<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;
use Illuminate\Support\Facades\DB;



return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $createdResource = Resource::where('segmen_url', 'penentuan-nominasi')->first();

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL])->get();
        $permission = [
            'bisa_get' => false,
            'bisa_post' => false,
            'bisa_put' => false,
            'bisa_delete' => false,
        ];

        try {
            foreach ($role as $r) {
                DB::table('gate.role_akses')->updateOrInsert(
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id],
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id, ...$permission]
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
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

        $createdResource = Resource::where('segmen_url', 'penentuan-nominasi')->first();

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL])->get();

        try {
            foreach ($role as $r) {
                DB::table('gate.role_akses')->where('id_role', $r->id)->where('id_resource', $createdResource->id)->delete();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }
};
