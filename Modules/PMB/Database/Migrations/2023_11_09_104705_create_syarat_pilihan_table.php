<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\Syarat;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.syarat_pilihan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Syarat::class);
            $table->string('nama_pilihan');
            $table->decimal('poin_pilihan', 5, 2, true);
            $table->logs(true);
        });

        SevimaSchema::table('pmb.syarat_pilihan', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_syarat', 'nama_pilihan'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.syarat_pilihan');
    }
};
