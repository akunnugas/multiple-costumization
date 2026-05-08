<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $listPenilaianPanduan = PenilaianPanduan::where('apakah_data_default', true)
            ->get();
        foreach ($listPenilaianPanduan as $penilaianPanduan) {
            PenilaianMatriks::resyncTreeStructure($penilaianPanduan->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
