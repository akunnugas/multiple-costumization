<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('klien', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_klien');
            $table->string('kode_klien', 50);
            $table->string('kode_dikti', 10)->nullable();
            $table->logs(true);
            $table->index('kode_dikti');
        });

        SevimaSchema::table('klien', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_klien', true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('klien');
    }
};
