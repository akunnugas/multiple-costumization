<?php

use Illuminate\Database\Migrations\Migration;
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
        SevimaSchema::create('pmb.jalur_pendaftaran', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_jalur');
            $table->string('keterangan_jalur')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('pmb.jalur_pendaftaran', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_jalur', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.jalur_pendaftaran');
    }
};
