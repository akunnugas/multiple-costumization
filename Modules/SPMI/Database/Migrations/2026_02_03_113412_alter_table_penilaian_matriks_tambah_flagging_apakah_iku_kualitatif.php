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
        SevimaSchema::table('spmi.penilaian_panduan', function (Blueprint $table) {
            if (!Schema::hasColumn('spmi.penilaian_panduan', 'apakah_iku_kualitatif')) {
                $table->boolean('apakah_iku_kualitatif')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::table('spmi.penilaian_panduan', function (Blueprint $table) {
            if (Schema::hasColumn('spmi.penilaian_panduan', 'apakah_iku_kualitatif')) {
                $table->dropColumn('apakah_iku_kualitatif');
            }
        });
    }
};
