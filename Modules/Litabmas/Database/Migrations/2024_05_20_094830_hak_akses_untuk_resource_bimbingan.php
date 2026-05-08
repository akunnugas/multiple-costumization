<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\ResourceAksi;
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
        DB::beginTransaction();

        // [Start] create main resource & permission
        $resourcesData = [
            'nama_resource' => 'Daftar Bimbingan',
            'segmen_url' => 'bimbingan',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'bimbingan',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        $role = Role::where('kode_role', ROLE::ROLE_DOSEN)->first();
        $permission = [
            'bisa_get' => true,
            'bisa_post' => false,
            'bisa_put' => false,
            'bisa_delete' => false,
        ];

        try {
            DB::table('gate.role_akses')->updateOrInsert(
                ['id_role' => $role->id, 'id_resource' => $createdResource->id],
                ['id_role' => $role->id, 'id_resource' => $createdResource->id, ...$permission]
            );
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // [Start] custom permission & resource
        $customAksi = ResourceAksi::firstOrCreate([
            'id_resource' => $createdResource->id,
            'kode_aksi' => 'feedback-akvts-pnltn',
        ], [
            'nama_aksi' => 'Feedback Aktivitas Peneliti',
        ]);

        try {
            DB::table('gate.role_akses_aksi')->updateOrInsert([
                'id_role' => $role->id,
                'id_resource_aksi' => $customAksi->id,
            ], [
                'id_role' => $role->id,
                'id_resource_aksi' => $customAksi->id,
            ]);
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

        $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        $resource = Resource::where('segmen_url', 'bimbingan')->where('id_modul', $idModul)->first();
        $role = Role::where('kode_role', ROLE::ROLE_DOSEN)->first();

        try {
            // custom resource
            $customAksi = ResourceAksi::where('id_resource', $resource->id)
                ->where('kode_aksi', 'feedback-akvts-pnltn')->first();
            DB::table('gate.role_akses_aksi')->where('id_resource_aksi', $customAksi->id)->delete();

            // main resource
            DB::table('gate.role_akses')->where('id_role', $role->id)
                ->where('id_resource', $resource->id)->delete();
            $resource->delete();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }
};
