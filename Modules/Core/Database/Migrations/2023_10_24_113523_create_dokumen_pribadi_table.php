<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenisDokumen;
use Modules\Gate\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.dokumen_pribadi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Biodata::class);
            $table->unsignedBigInteger('id_dokumen');
            $table->foreignIdTo(JenisDokumen::class);
            $table->string('status_dokumen', 20)->default('not_valid');
            $table->timestampTz('waktu_validasi')->nullable();
            $table->foreignIdTo(User::class, 'divalidasi_oleh', true);
            $table->string('catatan_validasi')->nullable();
            $table->logs(false);
            $table->index('id_dokumen');
        });

        SevimaSchema::table('core.dokumen_pribadi', function (SevimaBlueprint $table) {
            $table->foreign('id_dokumen')->references('id')->on('dms.dokumen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.dokumen_pribadi');
    }
};
