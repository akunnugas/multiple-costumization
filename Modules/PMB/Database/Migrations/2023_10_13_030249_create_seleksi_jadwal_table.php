<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\Seleksi;
use Modules\PMB\Models\SeleksiRuangan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.seleksi_jadwal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SeleksiRuangan::class);
            $table->foreignIdTo(Seleksi::class);
            $table->timestampTz('waktu_mulai')->nullable();
            $table->timestampTz('waktu_selesai')->nullable();
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.seleksi_jadwal');
    }
};
