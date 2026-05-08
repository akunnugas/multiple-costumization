<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\MataPelajaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.penilaian_rapor', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(MataPelajaran::class);
            $table->string('kode_penilaian', 20);
            $table->string('nama_penilaian');
            $table->char('jenis_penilaian', 1);
            $table->string('keterangan_penilaian', 500)->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('pmb.penilaian_rapor', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_penilaian', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.penilaian_rapor');
    }
};
