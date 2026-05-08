<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\Mitra;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.kontak', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Mitra::class);

            $table->string('nama_kontak');
            $table->string('jabatan');
            $table->string('telepon');
            $table->string('email');
            
            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('kerjasama.kontak');
    }
};
