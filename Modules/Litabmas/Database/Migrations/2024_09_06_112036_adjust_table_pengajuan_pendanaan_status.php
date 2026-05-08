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
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_status');

        SevimaSchema::create('litabmas.pengajuan_pendanaan_status', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class);
            $table->foreignIdTo(\Modules\Litabmas\Models\AgendaKegiatan::class);
            $table->string('status');
            $table->logs();
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
