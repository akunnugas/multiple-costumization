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

        // [Start] resource penilaian isian proposal
        $resourcesData = [
            'nama_resource' => 'Penilaian Isian Proposal',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'penilaian-isian',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL])->get();
        $permission = [
            'bisa_get' => true,
            'bisa_post' => false,
            'bisa_put' => true,
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
        // [END] resource penilaian isian proposal

        // [Start] sub resource penilaian aspek proposal (komposisi)
        try {
            $subResource = ResourceAksi::firstOrCreate([
                'id_resource' => $createdResource->id,
                'kode_aksi' => 'penilaian-komposisi',
            ], [
                'nama_aksi' => 'Penilaian Aspek Proposal',
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
        // [END] sub resource penilaian aspek proposal (komposisi)

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
        $resource = Resource::where('segmen_url', 'penilaian-isian')->where('id_modul', $idModul)->first();
        $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL])->get();

        // delete sub resource
        try {
            $subResource = ResourceAksi::where('id_resource', $resource->id)
                ->where('kode_aksi', 'penilaian-komposisi')->first();

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
