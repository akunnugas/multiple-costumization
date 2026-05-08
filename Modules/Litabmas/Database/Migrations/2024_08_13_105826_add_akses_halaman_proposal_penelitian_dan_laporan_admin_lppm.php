<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Gate\Models\Resource;
use Modules\Gate\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
        Resource::firstOrCreate([
            'id_modul' => $idModul,
            'nama_resource' => 'Laporan Pendanaan Kegiatan',
            'segmen_url' => 'laporan-pendanaan-kegiatan',
        ]);
        Resource::firstOrCreate([
            'id_modul' => $idModul,
            'nama_resource' => 'Laporan Pengajuan Proposal',
            'segmen_url' => 'laporan-pengajuan-proposal',
        ]);

        $data = [
            'pengajuan-pendanaan' => [
                'bisa_get' => true,
                'bisa_post' => false,
                'bisa_put' => true,
                'bisa_delete' => true,
            ],
            'laporan-pendanaan-kegiatan' => [
                'bisa_get' => true,
                'bisa_post' => true,
                'bisa_put' => true,
                'bisa_delete' => true,
            ],
            'laporan-pengajuan-proposal' => [
                'bisa_get' => true,
                'bisa_post' => true,
                'bisa_put' => true,
                'bisa_delete' => true,
            ]
        ];

        $resources = Resource::whereIn('segmen_url', array_keys($data))->get();

        // set hak akses
        $role = Role::whereIn('kode_role', [ROLE::ROLE_LITABMAS_ADMIN_LPPM])->get();

        try {
            foreach ($role as $r) {
                foreach ($resources as $resource) {
                    DB::table('gate.role_akses')->updateOrInsert(
                        ['id_role' => $r->id, 'id_resource' => $resource->id],
                        ['id_role' => $r->id, 'id_resource' => $resource->id, ...$data[$resource->segmen_url]]
                    );
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
