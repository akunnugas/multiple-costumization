<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.mahasiswa', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Biodata::class);
            $table->string('nim', 24)->nullable();
            $table->string('nama_mahasiswa');
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.mahasiswa', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nim', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.mahasiswa');
    }
};
