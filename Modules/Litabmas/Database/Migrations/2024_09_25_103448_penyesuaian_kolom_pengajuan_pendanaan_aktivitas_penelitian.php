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
        Schema::table('litabmas.pengajuan_pendanaan_aktivitas_penelitian', function (Blueprint $table) {
            $table->dropColumn('id_jenis_aktivitas');
            $table->string('jenis_aktivitas')->after('id_pengajuan_pendanaan');
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
