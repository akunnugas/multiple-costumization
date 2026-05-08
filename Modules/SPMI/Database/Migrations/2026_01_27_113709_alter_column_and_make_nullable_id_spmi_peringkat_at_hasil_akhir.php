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
        Schema::table('spmi.hasil_akhir_audit', function (Blueprint $table) {
            $table->unsignedBigInteger('id_spmi_peringkat')->nullable()->change();
            $table->boolean('apakah_status_terakreditasi')->default(false)->after('id_akreditasi_peringkat');
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
