<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\Aktivitas;
use Modules\PMB\Models\Pendaftar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.log_ods', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Pendaftar::class);
            $table->foreignIdTo(Aktivitas::class);
            $table->string('keterangan_log')->nullable();
            $table->logs(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.log_ods');
    }
};
