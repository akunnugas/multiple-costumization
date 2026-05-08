<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\StatusPerkawinan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.status_perkawinan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_status_perkawinan');
            $table->string('kode_status_perkawinan', 5);
            $table->logs(true);
        });

        SevimaSchema::table('core.status_perkawinan', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_status_perkawinan', true);
        });

        // Insert data default
        StatusPerkawinan::create([
            'nama_status_perkawinan' => 'Duda/Janda',
            'kode_status_perkawinan' => 'D',
        ]);

        StatusPerkawinan::create([
            'nama_status_perkawinan' => 'Menikah',
            'kode_status_perkawinan' => 'M',
        ]);

        StatusPerkawinan::create([
            'nama_status_perkawinan' => 'Belum Pernah Menikah',
            'kode_status_perkawinan' => 'S',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.status_perkawinan');
    }
};
