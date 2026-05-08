<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.akreditasi_buku', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_buku');
            $table->string('kode_buku', 5);
            $table->enum('jenis_buku', ['pr', 'se'])->default('pr')->comment('pr = performance report | se = self evaluation');

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
        SevimaSchema::dropIfExists('spmi.akreditasi_buku');
    }
};
