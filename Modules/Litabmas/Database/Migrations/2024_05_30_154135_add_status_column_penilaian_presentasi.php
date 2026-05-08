<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Support\Facades\DB;

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
        try {
            SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
                $table->string('status_progres_penilaian_presentasi', 25)->comment('Status')->nullable();
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
            SevimaSchema::table('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
                $table->dropColumn('status_progres_penilaian_presentasi');
            });
        } catch (\Exception $e) {
            DB::rollBack();
        }
        DB::commit();
    }
};
