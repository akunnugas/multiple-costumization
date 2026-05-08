<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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

        // create resource
        $resourcesData = [
            'nama_resource' => 'Validasi Dokumen',
            'segmen_url' => 'penilaian-administrasi',
        ];

        try {
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');
            $createdResource = Resource::firstOrCreate([
                'id_modul' => $idModul,
                'segmen_url' => 'penilaian-administrasi',
            ], $resourcesData);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // set hak akses
        $role = Role::where('kode_role', ROLE::ROLE_LITABMAS_ADMIN_LPPM)->first();
        $permission = $this->getRolePermissions('get', 'update');

        try {
            DB::table('gate.role_akses')->updateOrInsert(
                ['id_role' => $role->id, 'id_resource' => $createdResource->id],
                ['id_role' => $role->id, 'id_resource' => $createdResource->id, ...$permission]
            );
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
        $resource = Resource::where('segmen_url', 'penilaian-administrasi')->where('id_modul', $idModul)->first();
        $role = Role::where('kode_role', ROLE::ROLE_LITABMAS_ADMIN_LPPM)->first();

        try {
            DB::table('gate.role_akses')->where('id_role', $role->id)->where('id_resource', $resource->id)->delete();
            $resource->delete();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }

    private function getRolePermissions(...$permissions)
    {
        $permissionData = array_filter(array_map(function ($permission) {
            if ($permission == 'get') {
                return ['key' => 'bisa_get', 'act' => true];
            } else if ($permission == 'create') {
                return ['key' => 'bisa_post', 'act' => true];
            } else if ($permission == 'update') {
                return ['key' => 'bisa_put', 'act' => true];
            } else if ($permission == 'delete') {
                return ['key' => 'bisa_delete', 'act' => true];
            }

            return null;
        }, $permissions));

        $permissionData = array_column(array_values($permissionData), 'act', 'key');

        foreach (['bisa_get', 'bisa_post', 'bisa_put', 'bisa_delete'] as $permission) {
            if (!isset($permissionData[$permission])) {
                $permissionData[$permission] = false;
            }
        }

        return $permissionData;
    }
};
