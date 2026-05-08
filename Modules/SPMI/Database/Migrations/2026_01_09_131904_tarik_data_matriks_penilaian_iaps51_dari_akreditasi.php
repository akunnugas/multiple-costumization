<?php

use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\JenisStandar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Helpers\AccreditationSync;
use Modules\SPMI\Services\MigrationService;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $accreditationSync = new AccreditationSync();
            $assessmentMatrices = $accreditationSync->getAssessmentMatrices(
                "IAPS5.1-S1-Akre"
            );
            $assessmentMatrices = $assessmentMatrices["data"] ?? [];

            [$err, $msg] = MigrationService::migratePenilaianMatrix(
                "IAPS5.1-S1-Akre",
                $assessmentMatrices
            );
        } catch (\Exception $e) {
            info("Migration error: " . $e->getMessage());
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
