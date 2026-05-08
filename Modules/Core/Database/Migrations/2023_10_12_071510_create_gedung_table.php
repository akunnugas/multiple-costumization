<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Kampus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.gedung', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_gedung');
            $table->string('nama_gedung');
            $table->string('alamat_gedung')->nullable();
            $table->string('telepon_gedung')->nullable();
            $table->integer('jumlah_lantai')->nullable();
            $table->integer('jumlah_ruangan')->nullable();
            $table->foreignIdTo(Kampus::class, nullable: true);
            $table->logs(true);
        });

        SevimaSchema::table('core.gedung', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_gedung', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.gedung');
    }
};
