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
        SevimaSchema::table('litabmas.pengumuman_pendanaan', function (SevimaBlueprint $table) {
            $table->foreignId('id_periode_pendanaan')->nullable()->comment('Periode Pendanaan')->constrained('litabmas.periode_pendanaan')->onDelete('set null');
            $table->timestamp('waktu_dipublikasi')->nullable()->comment('Waktu pengumuman dipublikasi');
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
