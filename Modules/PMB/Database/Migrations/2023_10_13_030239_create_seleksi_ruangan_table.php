<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Ruangan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.seleksi_ruangan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_ruangan');
            $table->string('nama_ruangan');
            $table->integer('daya_tampung')->nullable();
            $table->foreignIdTo(Ruangan::class, nullable: true);
            $table->logs(true);
        });

        SevimaSchema::table('pmb.seleksi_ruangan', function (SevimaBlueprint $table) {
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
        SevimaSchema::dropIfExists('pmb.seleksi_ruangan');
    }
};
