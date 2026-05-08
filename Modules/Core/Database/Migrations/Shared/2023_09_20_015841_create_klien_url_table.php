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
        SevimaSchema::create('klien_url', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Klien::class);
            $table->string('url');
            $table->string('url_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('klien_url', function (SevimaBlueprint $table) {
            $table->uniqueIndex('url', true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('klien_url');
    }
};
