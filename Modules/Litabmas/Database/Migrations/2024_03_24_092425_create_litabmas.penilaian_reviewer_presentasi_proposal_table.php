<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\AspekPenilaianPresentasiProposal;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.penilaian_reviewer_presentasi_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(null, 'id_pengajuan_pendanaan_reviewer',
                foreignName: 'litabmas_prpp_id_pengajuan_pendanaan_reviewer',
                indexName: 'prpp_id_pengajuan_pendanaan_reviewer_foreign',
                table: 'litabmas.pengajuan_pendanaan_reviewer'
            );
            $table->foreignIdTo(AspekPenilaianPresentasiProposal::class, 'id_aspek_penilaian_presentasi_proposal',
                foreignName: 'litabmas_prpp_id_aspek_penilaian_presentasi_proposal', indexName: 'prpp_id_aspek_penilaian_presentasi_proposal_foreign'
            );
            $table->unsignedTinyInteger('skala_nilai_presentasi_proposal')->nullable()->comment('Nilai');

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
        SevimaSchema::dropIfExists('litabmas.penilaian_reviewer_presentasi_proposal');
    }
};
