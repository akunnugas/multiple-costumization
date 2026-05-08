<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $resourcesData = [
            'nama_resource' => 'Penentuan Nominasi',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'penentuan-nominasi',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->get();
        $permissionadmin = [
            'bisa_get' => true,
            'bisa_post' => true,
            'bisa_put' => true,
            'bisa_delete' => false,
        ];

        try {
            foreach ($role as $r) {
                DB::table('gate.role_akses')->updateOrInsert(
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id],
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id, ...$permissionadmin]
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses ketua lppm
        $role = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_KETUA_LPPM])->get();
        $permissionketua = [
            'bisa_get' => true,
            'bisa_post' => true,
            'bisa_put' => true,
            'bisa_delete' => false,
        ];

        try {
            foreach ($role as $r) {
                DB::table('gate.role_akses')->updateOrInsert(
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id],
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id, ...$permissionketua]
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

        try {
            // Get the resource ID that was created
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::where([
                'id_modul' => $idModul,
                'segmen_url' => 'penentuan-nominasi',
            ])->first();

            if ($createdResource) {
                // Delete the role accesses related to the resource
                DB::table('gate.role_akses')->where('id_resource', $createdResource->id)->delete();

                // Delete the created resource
                $createdResource->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
};
