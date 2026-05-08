<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\AgendaKegiatan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        AgendaKegiatan::where('kode_agenda', 'pengumuman_lolos_nominasi')
            ->update(['apakah_wajib' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AgendaKegiatan::where('kode_agenda', 'pengumuman_lolos_nominasi')
            ->update(['apakah_wajib' => false]);
    }
};
