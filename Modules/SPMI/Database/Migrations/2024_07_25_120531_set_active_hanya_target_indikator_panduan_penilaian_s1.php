<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\MigrationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        PenilaianPanduan::where('apakah_aktif', true)->update(['apakah_aktif' => false]);
        MigrationService::setActivePanduanPenilaian('IAPS-S1', true, true);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        PenilaianPanduan::where('apakah_aktif', true)->update(['apakah_aktif' => false]);
        MigrationService::setActivePanduanPenilaian('IAPS-S1', false, true);

        DB::commit();
    }
};
