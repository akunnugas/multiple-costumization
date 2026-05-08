<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Shared\Klien;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('klien_config', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Klien::class);
            $table->string('nama_db');
            $table->string('username_db');
            $table->string('password_db');
            $table->string('timezone')->nullable();
            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('klien_config');
    }
};
