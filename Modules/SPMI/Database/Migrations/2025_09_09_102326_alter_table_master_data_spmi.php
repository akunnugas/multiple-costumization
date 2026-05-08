<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::table('spmi.jenis_standar', function (SevimaBlueprint $table) {
            $table->boolean('apakah_data_default')->default(true);
        });

        SevimaSchema::table('spmi.akreditasi_standar', function (SevimaBlueprint $table) {
            $table->boolean('apakah_data_default')->default(true);
        });

        SevimaSchema::table('spmi.akreditasi_buku', function (SevimaBlueprint $table) {
            $table->boolean('apakah_data_default')->default(true);
        });

        SevimaSchema::table('spmi.akreditasi_syarat', function (SevimaBlueprint $table) {
            $table->boolean('apakah_data_default')->default(true);
        });

        SevimaSchema::table('core.lembaga_akreditasi', function (SevimaBlueprint $table) {
            $table->boolean('apakah_data_default')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
