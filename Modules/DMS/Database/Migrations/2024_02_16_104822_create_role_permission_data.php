<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $id = DB::select("select id from gate.modul where kode_modul = 'dms'");

        $roleAdminDMS = DB::table('gate.role')->where('kode_role', Role::ROLE_ADMINDMS)->first();
        $roleAdminPT = DB::table('gate.role')->where('kode_role', Role::ROLE_ADMINPT)->first();

        $resources = [
            [
                'id_modul' => $id,
                'nama_resource' => 'Beranda',
                'segmen_url' => ''
            ],
            [
                'id_modul' => $id,
                'nama_resource' => 'User Files',
                'segmen_url' => 'user-files'
            ],
            [
                'id_modul' => $id,
                'nama_resource' => 'Folders',
                'segmen_url' => 'folders'
            ],
            [
                'id_modul' => $id,
                'nama_resource' => 'Files',
                'segmen_url' => 'files'
            ]
        ];
        $permissions = [
            'bisa_get' => true,
            'bisa_post' => true,
            'bisa_put' => true,
            'bisa_delete' => true
        ];

        DB::beginTransaction();

        foreach ($resources as $resource) {
            $moduleId = $resource['id_modul'][0]?->id ?? null;
            $resource['id_modul'] = $moduleId;
            $id = Resource::updateOrCreate(
                ['id_modul' => $moduleId, 'nama_resource' => $resource['nama_resource']],
                $resource
            )->id;

            DB::table('gate.role_akses')->updateOrInsert(
                ['id_role' => $roleAdminDMS->id, 'id_resource' => $id],
                $permissions
            );

            DB::table('gate.role_akses')->updateOrInsert(
                ['id_role' => $roleAdminPT->id, 'id_resource' => $id],
                $permissions
            );
        }

        DB::commit();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
