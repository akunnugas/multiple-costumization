<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP INDEX IF EXISTS spmi.akreditasi_peringkat_kode_peringkat_idx;');
        SevimaSchema::table('spmi.akreditasi_peringkat', function (SevimaBlueprint $table) {
            $table->unsignedBigInteger('id_penilaian_panduan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.akreditasi_peringkat', function (SevimaBlueprint $table) {
            $table->dropColumn('id_penilaian_panduan');
        });

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS akreditasi_peringkat_kode_peringkat_idx ON spmi.akreditasi_peringkat (kode_peringkat);');
    }
};
