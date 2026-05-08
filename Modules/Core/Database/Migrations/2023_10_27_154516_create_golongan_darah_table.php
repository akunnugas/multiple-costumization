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
        SevimaSchema::create('core.golongan_darah', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_golongan', 10);
            $table->logs(true);
        });

        SevimaSchema::table('core.golongan_darah', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_golongan', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.golongan_darah');
    }
};
