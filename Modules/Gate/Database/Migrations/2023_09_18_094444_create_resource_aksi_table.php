<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\Resource;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.resource_aksi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Resource::class);
            $table->string('nama_aksi');
            $table->string('kode_aksi', 20);
            $table->logs(true);
        });

        SevimaSchema::table('gate.resource_aksi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_resource', 'kode_aksi'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.resource_aksi');
    }
};
