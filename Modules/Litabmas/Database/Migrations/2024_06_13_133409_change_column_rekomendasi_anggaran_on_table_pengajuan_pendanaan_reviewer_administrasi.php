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
        DB::statement('ALTER TABLE litabmas.pengajuan_pendanaan_reviewer_administrasi ALTER COLUMN rekomendasi_anggaran TYPE NUMERIC(15, 2) USING rekomendasi_anggaran::numeric(15, 2)');
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE litabmas.pengajuan_pendanaan_reviewer_administrasi ALTER COLUMN rekomendasi_anggaran TYPE NUMERIC(15, 2) USING rekomendasi_anggaran::numeric(15, 2)');
    }
};
