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

        //delete old resource

        // Assuming the resource needs to be deleted based on the 'segmen_url'
        $segmenUrl = 'klaster-penelitian';

        try {
            // Get the resource to delete
            $resource = Resource::where('segmen_url', $segmenUrl)->first();

            if ($resource) {
                // Delete the role access permissions associated with this resource
                DB::table('gate.role_akses')->where('id_resource', $resource->id)->delete();

                // Delete the resource itself
                $resource->delete();
            }

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            // Handle the exception as needed (e.g., logging or returning an error response)
        }


        //create new resource
        $resourcesData = [
            'nama_resource' => 'Pengumuman Klaster',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'pengumuman-klaster',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses
        $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL])->get();
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
            // Delete the newly created resource based on the 'segmen_url'
            $segmenUrl = 'pengumuman-klaster';

            // Get the resource to delete
            $resource = Resource::where('segmen_url', $segmenUrl)->first();

            if ($resource) {
                // Delete the role access permissions associated with this resource
                DB::table('gate.role_akses')->where('id_resource', $resource->id)->delete();

                // Delete the resource itself
                $resource->delete();
            }

            // Re-create the old resource
            $resourcesData = [
                'nama_resource' => 'Klaster Penelitian',
            ];

            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'klaster-penelitian',
            ], $resourcesData);

            // Re-set hak akses for the old resource
            $role = Role::whereIn('kode_role', [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL])->get();
            $permission = [
                'bisa_get' => true,
                'bisa_post' => true,
                'bisa_put' => true,
                'bisa_delete' => true,
            ];

            foreach ($role as $r) {
                DB::table('gate.role_akses')->updateOrInsert(
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id],
                    ['id_role' => $r->id, 'id_resource' => $createdResource->id, ...$permission]
                );
            }

            // Commit the transaction if everything goes smoothly
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            // Handle the exception as needed (e.g., logging or returning an error response)
        }
    }

};
