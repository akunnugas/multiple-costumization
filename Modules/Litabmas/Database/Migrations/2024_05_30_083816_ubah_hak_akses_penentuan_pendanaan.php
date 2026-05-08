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
        //update hak akses resource penentuan pendanaan
        $resourcePenentuanPendanaan = Resource::where('segmen_url', 'penentuan-pendanaan')->first('id');
        $resourceAksiManageReviewer = ResourceAksi::where('kode_aksi', 'manage-reviewer')->where('id_resource', $resourcePenentuanPendanaan->id)->first('id');

        $roleDosen = Role::whereIn('kode_role', [Role::ROLE_DOSEN])->first('id');
        $roleAdminLitabmas = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->first('id');

        try {
            DB::table('gate.role_akses')->where('id_role', $roleDosen->id)->where('id_resource', $resourcePenentuanPendanaan->id)->update([
                'id_role' => $roleAdminLitabmas->id,
            ]);

            DB::table('gate.resource_aksi')->where('id', $resourceAksiManageReviewer->id)->update([
                'kode_aksi' => 'manage-pembimbing',
                'nama_aksi' => 'Manage Pembimbing',
            ]);

            DB::table('gate.role_akses_aksi')->where('id_role', $roleDosen->id)->where('id_resource_aksi', $resourceAksiManageReviewer->id)->update([
                'id_role' => $roleAdminLitabmas->id,
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
        //update hak akses resource penentuan pendanaan
        $resourcePenentuanPendanaan = Resource::where('segmen_url', 'penentuan-pendanaan')->first('id');
        $resourceAksiManageReviewer = ResourceAksi::where('kode_aksi', 'manage-pembimbing')->where('id_resource', $resourcePenentuanPendanaan->id)->first('id');

        $roleDosen = Role::whereIn('kode_role', [Role::ROLE_DOSEN])->first('id');
        $roleAdminLitabmas = Role::whereIn('kode_role', [Role::ROLE_LITABMAS_ADMIN_LPPM])->first('id');

        try {
            DB::table('gate.role_akses')->where('id_role', $roleAdminLitabmas->id)->where('id_resource', $resourcePenentuanPendanaan->id)->update([
                'id_role' => $roleDosen->id,
            ]);

            DB::table('gate.resource_aksi')->where('id', $resourceAksiManageReviewer->id)->update([
                'kode_aksi' => 'manage-reviewer',
                'nama_aksi' => 'Manage Reviewer',
            ]);

            DB::table('gate.role_akses_aksi')->where('id_role', $roleAdminLitabmas->id)->where('id_resource_aksi', $resourceAksiManageReviewer->id)->update([
                'id_role' => $roleDosen->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }
};
