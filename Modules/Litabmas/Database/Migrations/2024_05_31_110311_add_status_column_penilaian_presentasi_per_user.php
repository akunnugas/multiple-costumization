<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        try {
            SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
                $table->string('status_penilaian_presentasi', 25)->comment('Status Penilaian Presentasi Per User')->nullable();
            });
        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();

        try {
            SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
                $table->dropColumn('status_progres_penilaian_presentasi');
            });
        } catch (\Exception $e) {
            DB::rollBack();
        }
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
        try {
            SevimaSchema::table('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
                $table->dropColumn('status_penilaian_presentasi');
            });
        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();

        try {
            SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
                $table->string('status_progres_penilaian_presentasi', 25)->comment('Status Progres Penilaian Presentasi')->nullable();
            });
        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }
};
