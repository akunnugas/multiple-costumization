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
            $table->unsignedBigInteger('id_status_peringkat')->nullable();
        });

        Schema::table('spmi.hasil_akhir_audit', function (Blueprint $table) {
            $table->foreign('id_status_peringkat', 'fk_hasil_akhir_audit_to_akreditasi_status')
                ->references('id')->on('spmi.akreditasi_status')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
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
