<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
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
        SevimaSchema::create('litabmas.periode_pendanaan', function (SevimaBlueprint $table) {
            $table->id()->comment('ID Periode Penelitian');
            $table->string('tahun',4)->comment('Periode');
            $table->index('tahun');
            $table->date('tanggal_mulai')->nullable()->comment('Tanggal Mulai Periode');
            $table->date('tanggal_akhir')->nullable()->comment('Tanggal Akhir Periode');
            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX ON litabmas.periode_pendanaan (tahun) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.periode_pendanaan');
    }
};
