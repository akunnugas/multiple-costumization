<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\UnitKerja;
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
        SevimaSchema::create('dms.dokumen_unit', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(DokumenPerizinan::class, 'id_dokumen_perizinan');
            $table->foreignIdTo(UnitKerja::class, 'id_unit_kerja');
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
        SevimaSchema::dropIfExists('dms.dokumen_unit');
    }
};
