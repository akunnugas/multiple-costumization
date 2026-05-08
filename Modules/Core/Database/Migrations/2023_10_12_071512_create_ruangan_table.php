<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Gedung;
use Modules\Core\Models\JenisRuangan;
use Modules\Core\Models\UnitKerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.ruangan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_ruangan');
            $table->string('nama_ruangan');
            $table->string('lokasi')->nullable();
            $table->integer('daya_tampung')->nullable();
            $table->integer('panjang')->nullable();
            $table->integer('lebar')->nullable();
            $table->integer('lantai')->nullable();
            $table->foreignIdTo(UnitKerja::class);
            $table->foreignIdTo(Gedung::class, nullable: true);
            $table->foreignIdTo(JenisRuangan::class, nullable: true);
            $table->logs(true);
        });

        SevimaSchema::table('core.ruangan', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_ruangan', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.ruangan');
    }
};
