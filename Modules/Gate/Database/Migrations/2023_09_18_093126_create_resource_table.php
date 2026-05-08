<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.resource', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Modul::class);
            $table->string('nama_resource');
            $table->string('segmen_url', 100);
            $table->logs(true);
        });

        SevimaSchema::table('gate.resource', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_modul', 'segmen_url'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.resource');
    }
};
