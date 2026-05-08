<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.akreditasi_status', function (Blueprint $table) {
            $table->id();
            $table->string('kode_status', 10);
            $table->string('nama_status', 255);
            $table->decimal('nilai_minimal', 5, 2);
            $table->decimal('nilai_maksimal', 5, 2);
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('id_penilaian_panduan');
            $table->foreign('id_penilaian_panduan')->references('id')->on('spmi.penilaian_panduan')->onDelete('cascade');
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.akreditasi_status');
    }
};
