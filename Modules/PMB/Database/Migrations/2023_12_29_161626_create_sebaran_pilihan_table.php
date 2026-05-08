<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\SebaranProdi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('pmb.sebaran_pilihan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SebaranProdi::class);
            $table->unsignedTinyInteger('pilihan');
            $table->logs(true);
        });

        SevimaSchema::table('pmb.sebaran_pilihan', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_sebaran_prodi', 'pilihan'], true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('pmb.sebaran_pilihan');
    }
};
