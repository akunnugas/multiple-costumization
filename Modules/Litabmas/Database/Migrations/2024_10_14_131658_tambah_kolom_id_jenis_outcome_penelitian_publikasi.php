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
        Schema::table('litabmas.pengajuan_pendanaan_publikasi_artikel', function (Blueprint $table) {
            $table->foreignId('id_jenis_outcome_penelitian')->constrained('litabmas.jenis_outcome_penelitian')->after('id_pengajuan_pendanaan');
        });

        Schema::table('litabmas.pengajuan_pendanaan_publikasi_buku', function (Blueprint $table) {
            $table->foreignId('id_jenis_outcome_penelitian')->constrained('litabmas.jenis_outcome_penelitian')->after('id_pengajuan_pendanaan');
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
