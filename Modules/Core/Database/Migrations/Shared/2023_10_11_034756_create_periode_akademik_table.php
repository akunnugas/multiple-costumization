<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Shared\TahunAkademik;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('periode_akademik', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_periode',10);
            $table->string('nama_periode');
            $table->foreignIdTo(TahunAkademik::class);
            $table->logs(true);
        });

        SevimaSchema::table('periode_akademik', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_periode', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('periode_akademik');
    }
};
