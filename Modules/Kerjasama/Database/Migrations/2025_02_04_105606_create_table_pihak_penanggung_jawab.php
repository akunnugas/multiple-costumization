<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\Kerjasama;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.pihak_penanggung_jawab', function (SevimaBlueprint $table) {
            $table->id();

            $table->foreignIdTo(Kerjasama::class);
            
            $table->integer('pihak_ke');
            $table->string('model_pihak');
            $table->unsignedBigInteger('id_pihak');
            $table->text('alamat')->nullable();

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
    }
};
