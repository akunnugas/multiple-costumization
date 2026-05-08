<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\AspekPenilaianOutputJawaban;
use Modules\Litabmas\Models\AspekPenilaianOutputPertanyaan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.penilaian_reviewer_output_bersama', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(null, 'id_pengajuan_pendanaan_reviewer',
                foreignName: 'litabmas_prob_id_pengajuan_pendanaan_reviewer',
                indexName: 'prob_id_pengajuan_pendanaan_reviewer_foreign',
                table: 'litabmas.pengajuan_pendanaan_reviewer'
            );
            $table->foreignIdTo(AspekPenilaianOutputPertanyaan::class, 'id_aspek_penilaian_output_pertanyaan',
                foreignName: 'litabmas_prob_id_aspek_penilaian_output_pertanyaan', indexName: 'prob_id_aspek_penilaian_output_pertanyaan_foreign'
            );
            $table->foreignIdTo(AspekPenilaianOutputJawaban::class, 'id_aspek_penilaian_output_jawaban',
                foreignName: 'litabmas_prob_id_aspek_penilaian_output_jawaban', indexName: 'prob_id_aspek_penilaian_output_jawaban_foreign'
            );

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('litabmas.penilaian_reviewer_output_bersama');
    }
};
