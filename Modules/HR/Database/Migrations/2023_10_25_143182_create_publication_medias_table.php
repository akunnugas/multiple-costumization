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
        SevimaSchema::create('hr.publication_medias', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama Media Publikasi');
            $table->string('sister_id')->nullable()->comment('ID Sister');

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
        SevimaSchema::dropIfExists('hr.publication_medias');
    }
};
