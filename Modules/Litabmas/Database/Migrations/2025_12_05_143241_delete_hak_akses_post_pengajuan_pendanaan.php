<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
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
        DB::beginTransaction();

        $resourcesData = [
            'nama_resource' => 'Riwayat Penelitian',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'pengajuan-pendanaan',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->get();
        $permission = [
            'bisa_get' => true,
            'bisa_post' => false,
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
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $role = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->get();
            $resource = Resource::where('id_modul', $idModul)
                ->where('segmen_url', 'pengajuan-pendanaan')
                ->first();

            foreach ($role as $r) {
                DB::table('gate.role_akses')->where('id_role', $r->id)
                    ->where('id_resource', $resource->id)
                    ->delete();
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }
};
