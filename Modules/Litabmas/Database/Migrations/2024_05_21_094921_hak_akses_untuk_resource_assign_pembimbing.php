<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\ResourceAksi;
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

        // [Start] resource penentuan pendanaan
        $resourcesData = [
            'nama_resource' => 'Penentuan Pendanaan',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'penentuan-pendanaan',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->get();
        $permission = [
            'bisa_get' => true,
            'bisa_post' => true,
            'bisa_put' => true,
            'bisa_delete' => true,
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
        // [END] resource penentuan pendanaan

        // [Start] sub resource penentuan pendanaan
        try {
            $subResource = ResourceAksi::firstOrCreate([
                'id_resource' => $createdResource->id,
                'kode_aksi' => 'manage-pembimbing',
            ], [
                'nama_aksi' => 'Manage Pembimbing',
            ]);

            foreach ($role as $r) {
                DB::table('gate.role_akses_aksi')->updateOrInsert([
                    'id_role' => $r->id,
                    'id_resource_aksi' => $subResource->id,
                ], [
                    'id_role' => $r->id,
                    'id_resource_aksi' => $subResource->id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
        // [END] sub resource penentuan pendanaan

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

        $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        $resource = Resource::where('segmen_url', 'penentuan-pendanaan')->where('id_modul', $idModul)->first();
        $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN])->get();

        // delete sub resource
        try {
            $subResource = ResourceAksi::where('id_resource', $resource->id)
                ->where('kode_aksi', 'manage-pembimbing')->first();

            foreach ($role as $r) {
                DB::table('gate.role_akses_aksi')
                    ->where('id_role', $r->id)
                    ->where('id_resource_aksi', $subResource->id)
                    ->delete();
            }
            $subResource->delete();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // delete resource
        try {
            foreach ($role as $r) {
                DB::table('gate.role_akses')
                    ->where('id_role', $r->id)
                    ->where('id_resource', $resource->id)
                    ->delete();
            }

            $resource->delete();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }
};
