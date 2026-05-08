<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Jobs\ProcessSyncIndikatorBobot;
use Modules\Core\Models\Shared\KlienConfig;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $kode_klien = KlienConfig::getKodeKlien();

        ProcessSyncIndikatorBobot::dispatch($kode_klien);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
