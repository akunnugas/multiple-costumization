<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        SevimaSchema::table('litabmas.periode_pendanaan', function (SevimaBlueprint $table) {
            // tambah 2 kolom
            $table->unsignedTinyInteger('maksimal_ketua_mendaftar')->nullable()
                ->comment('Maksimal ketua mendaftar di satu periode pendanaan');
            $table->unsignedTinyInteger('maksimal_anggota_mendaftar')->nullable()
                ->comment('Maksimal anggota mendaftar di satu periode pendanaan');
        });

        // set data lama
        DB::table('litabmas.periode_pendanaan')
            ->update([
                'maksimal_ketua_mendaftar' => 1,
                'maksimal_anggota_mendaftar' => 5
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.periode_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('maksimal_ketua_mendaftar');
            $table->dropColumn('maksimal_anggota_mendaftar');
        });
    }
};
