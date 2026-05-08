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

        $resources = Resource::whereIn('segmen_url', ['pengajuan-pengabdian'])->get();

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
                foreach ($resources as $resource) {
                    DB::table('gate.role_akses')->updateOrInsert(
                        ['id_role' => $r->id, 'id_resource' => $resource->id],
                        ['id_role' => $r->id, 'id_resource' => $resource->id, ...$permission]
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
