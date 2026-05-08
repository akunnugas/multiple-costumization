<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spmi.indikator_kolom', function (Blueprint $table) {
            $table->string('ref_key_akreditasi')->nullable();
        });

        Schema::table('spmi.indikator_baris', function (Blueprint $table) {
            $table->string('ref_key_akreditasi')->nullable();
            $table->text('nama')->change();
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
