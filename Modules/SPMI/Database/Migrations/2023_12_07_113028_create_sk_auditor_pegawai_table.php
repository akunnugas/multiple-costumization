<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\SPMI\Models\SkAuditor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.sk_auditor_pegawai', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SkAuditor::class, 'id_sk_auditor');
            $table->foreignIdTo(Biodata::class, 'id_personil');
            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.sk_auditor_pegawai');
    }
};
