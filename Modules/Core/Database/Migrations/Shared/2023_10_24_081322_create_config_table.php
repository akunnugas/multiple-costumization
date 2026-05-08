<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Shared\Config;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('config', function (SevimaBlueprint $table) {
            $table->integer('id')->primary();
            $table->json('ip_debug')->nullable();
        });

        Config::create(['id' => 1]);
        DB::connection('shared')->statement('ALTER TABLE config ADD CHECK (id = 1)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('config');
    }
};
