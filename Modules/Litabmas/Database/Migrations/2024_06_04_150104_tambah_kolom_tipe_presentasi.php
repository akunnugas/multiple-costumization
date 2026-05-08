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
        SevimaSchema::table('litabmas.pengajuan_pendanaan_jadwal_presentasi', function (SevimaBlueprint $table) {
            $table->string('tipe_presentasi', 100)->nullable()->comment('Tipe Presentasi');
        });

        SevimaSchema::table('litabmas.pengajuan_pendanaan_jadwal_presentasi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_pengajuan_pendanaan', 'tipe_presentasi'],
                uniqueIndexName: 'ppjp_pengajuan_pendanaan_tipe_presentasi_unique_index'
            );
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan_jadwal_presentasi', function (SevimaBlueprint $table) {
            $table->dropColumn('tipe_presentasi');
        });
    }
};
