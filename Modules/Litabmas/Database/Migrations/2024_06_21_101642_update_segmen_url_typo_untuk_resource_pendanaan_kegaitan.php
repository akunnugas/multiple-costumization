<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Gate\Models\Resource;

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
            $idModul = DB::table('gate.modul')->where('kode_modul', 'litabmas')->value('id');

            // update segmen_url pendanaan_kegiatan to pendanaan-kegiatan
            Resource::where('id_modul', $idModul)
                ->where('segmen_url', 'pendanaan_kegiatan')
                ->update(['segmen_url' => 'pendanaan-kegiatan']);
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

            // update segmen_url pendanaan-kegiatan to pendanaan_kegiatan
            Resource::where('id_modul', $idModul)
                ->where('segmen_url', 'pendanaan-kegiatan')
                ->update(['segmen_url' => 'pendanaan_kegiatan']);
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::commit();
    }
};
