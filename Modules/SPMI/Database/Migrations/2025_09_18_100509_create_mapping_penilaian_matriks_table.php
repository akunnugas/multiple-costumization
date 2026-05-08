<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.mapping_penilaian_matriks', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_penilaian_matriks')->constrained('spmi.penilaian_matriks');
            $table->foreignId('id_audit_periode')->constrained('spmi.audit_periode');
            $table->foreignId('id_unit')->constrained('core.unit_kerja');
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
        SevimaSchema::dropIfExists('spmi.mapping_penilaian_matriks');
    }
};
