<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_bisa_multi_ajuan')->nullable()->comment('Apakah klaster ini memungkinkan pengajuan lebih dari 1x per user');
            $table->integer('maksimal_ajuan_per_user')->nullable()->comment('Maksimal ajuan per user pada klaster ini');
        });

        // set data lama sebagai tidak bisa multi ajuan
        DB::table('litabmas.klaster_pendanaan')
            ->whereNull('apakah_bisa_multi_ajuan')
            ->update([
                'apakah_bisa_multi_ajuan' => false,
                'maksimal_ajuan_per_user' => 1
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.klaster_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_bisa_multi_ajuan');
            $table->dropColumn('maksimal_ajuan_per_user');
        });
    }
};
