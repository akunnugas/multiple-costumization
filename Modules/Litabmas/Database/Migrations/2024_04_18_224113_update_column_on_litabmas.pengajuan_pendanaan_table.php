<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->string('nama_pemilik_rekening')->nullable()->change();
            $table->string('nomor_rekening')->nullable()->change();
            $table->string('nama_bank')->nullable()->change();

            // drop column id_agenda_kegiatan ganti dengan status_agenda_kegiatan
            $table->dropColumn('id_agenda_kegiatan');
            $table->string('status_agenda_kegiatan', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->string('nama_pemilik_rekening')->nullable(false)->change();
            $table->string('nomor_rekening')->nullable(false)->change();
            $table->string('nama_bank')->nullable(false)->change();

            // drop column status_agenda_kegiatan ganti dengan id_agenda_kegiatan
            $table->dropColumn('status_agenda_kegiatan');
            $table->foreignIdTo(\Modules\Litabmas\Models\AgendaKegiatan::class, 'id_agenda_kegiatan', true);
        });
    }
};
