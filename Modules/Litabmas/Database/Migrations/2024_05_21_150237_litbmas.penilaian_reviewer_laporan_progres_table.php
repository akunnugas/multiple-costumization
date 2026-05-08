<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.penilaian_reviewer_laporan_progres', function (SevimaBlueprint $table) {
            $table->id();

            $table->foreignIdTo(null,
                foreignName: 'litabmas_prlp_id_pengajuan_pendanaan_reviewer',
                indexName: 'prlp_id_pengajuan_pendanaan_reviewer_foreign',
                table: 'litabmas.pengajuan_pendanaan_reviewer'
            );
            $table->foreignIdTo(PengajuanPendanaanLaporanProgres::class,
                foreignName: 'litabmas_prlp_id_pengajuan_pendanaan_laporan_progres',
                indexName: 'prlp_id_pengajuan_pendanaan_laporan_progres_foreign',
            );
            $table->string('status_laporan_progres_reviewer', 25)->comment('Status');
            $table->text('feedback_laporan_progres')->nullable()->comment('Feedback');
            $table->logs();
        });

        DB::statement('CREATE UNIQUE INDEX ON litabmas.penilaian_reviewer_laporan_progres (
            id_pengajuan_pendanaan_reviewer, id_pengajuan_pendanaan_laporan_progres
        ) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.penilaian_reviewer_laporan_progres');
    }
};
