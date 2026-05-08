<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\JenisInstitusi;
use Modules\PMB\Models\SebaranProdi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('pmb.sebaran_asal_pendaftar', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SebaranProdi::class);
            $table->foreignIdTo(JenisInstitusi::class);
            $table->logs(true);
        });

        SevimaSchema::table('pmb.sebaran_asal_pendaftar', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_sebaran_prodi', 'id_jenis_institusi'], true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('pmb.sebaran_asal_pendaftar');
    }
};
