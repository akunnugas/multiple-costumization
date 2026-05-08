<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Services\UnitKerjaManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add column to organization table
        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->unsignedBigInteger('id_pimpinan')->nullable();
            $table->foreign('id_pimpinan')->references('id')->on('core.pegawai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop column from organization table
        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->dropForeign(['id_pimpinan']);
            $table->dropColumn('id_pimpinan');
        });
    }
};
