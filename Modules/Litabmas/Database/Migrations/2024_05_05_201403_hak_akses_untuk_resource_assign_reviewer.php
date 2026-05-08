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

        try {
            $idLitabmasModul = DB::table('gate.modul')
                ->where('kode_modul', 'litabmas')->value('id');
            $idResource = Resource::where('segmen_url', 'penilaian-administrasi')
                ->where('id_modul', $idLitabmasModul)->value('id');

            $createdResource = ResourceAksi::firstOrCreate([
                'id_resource' => $idResource,
                'kode_aksi' => 'manage-reviewer',
            ], [
                'nama_aksi' => 'Hapus Reviewer',
            ]);

            $role = Role::where('kode_role', ROLE::ROLE_LITABMAS_ADMIN_LPPM)->first();

            DB::table('gate.role_akses_aksi')->updateOrInsert([
                'id_role' => $role->id,
                'id_resource_aksi' => $createdResource->id,
            ], [
                'id_role' => $role->id,
                'id_resource_aksi' => $createdResource->id,
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

        try {
            $idLitabmasModul = DB::table('gate.modul')
                ->where('kode_modul', 'litabmas')->value('id');
            $idResource = Resource::where('segmen_url', 'penilaian-administrasi')
                ->where('id_modul', $idLitabmasModul)->value('id');

            $resourceAksi = ResourceAksi::where('id_resource', $idResource)
                ->where('kode_aksi', 'manage-reviewer')->first();

            DB::table('gate.role_akses_aksi')->where('id_resource_aksi', $resourceAksi->id)->delete();
            $resourceAksi->delete();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }
};
