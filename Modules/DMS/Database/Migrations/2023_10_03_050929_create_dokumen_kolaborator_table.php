<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Models\DokumenPerizinan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('dms.dokumen_kolaborator', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(DokumenPerizinan::class, 'id_dokumen_perizinan');
            $table->unsignedBigInteger('id_user')->nullable()->comment('ID Pengguna');
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
        SevimaSchema::dropIfExists('dms.dokumen_kolaborator');
    }
};
