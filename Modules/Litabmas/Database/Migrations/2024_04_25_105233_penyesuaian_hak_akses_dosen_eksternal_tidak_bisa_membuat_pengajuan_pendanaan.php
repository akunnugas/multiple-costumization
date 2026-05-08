<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        [$idRoleDosenEksternal, $idResourcePengajuanPendanaan] = $this->getIds();

        // update resource can_post to false
        DB::table('gate.role_akses')
            ->where('id_role', $idRoleDosenEksternal)
            ->where('id_resource', $idResourcePengajuanPendanaan)
            ->update(['bisa_post' => false, 'bisa_delete' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        [$idRoleDosenEksternal, $idResourcePengajuanPendanaan] = $this->getIds();

        // update resource can_post to false
        DB::table('gate.role_akses')
            ->where('id_role', $idRoleDosenEksternal)
            ->where('id_resource', $idResourcePengajuanPendanaan)
            ->update(['bisa_post' => true, 'bisa_delete' => true]);
    }

    private function getIds()
    {
        $idRoleDosenEksternal = DB::table('gate.role')
            ->where('kode_role', \Modules\Gate\Models\Role::ROLE_DOSEN_EKSTERNAL)->value('id');

        $idModul = DB::table('gate.modul')
            ->where('kode_modul', \Modules\Gate\Models\Modul::CODE_LITABMAS)->value('id');
        $idResourcePengajuanPendanaan = DB::table('gate.resource')
            ->where('id_modul', $idModul)
            ->where('segmen_url', 'pengajuan-pendanaan')
            ->value('id');

        return [
            $idRoleDosenEksternal,
            $idResourcePengajuanPendanaan
        ];
    }
};
