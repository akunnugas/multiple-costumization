<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Shared\PerguruanTinggi;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('program_studi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_prodi');
            $table->string('nama_prodi');
            $table->string('alamat_prodi')->nullable();
            $table->string('telepon_prodi')->nullable();
            $table->string('kode_sister')->nullable();
            $table->foreignIdTo(PerguruanTinggi::class);
            $table->logs(true);
        });

        SevimaSchema::table('program_studi', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_prodi', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('program_studi');
    }
};
