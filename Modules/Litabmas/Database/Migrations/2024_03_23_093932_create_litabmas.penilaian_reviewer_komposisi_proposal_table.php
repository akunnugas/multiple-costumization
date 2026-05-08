<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\AspekPenilaianKomposisiProposal;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.penilaian_reviewer_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->foreignIdTo(AspekPenilaianKomposisiProposal::class, 'id_aspek_penilaian_komposisi_proposal',
                foreignName: 'litabmas_prob_id_aspek_penilaian_komposisi_proposal',
                indexName: 'prob_id_aspek_penilaian_komposisi_proposal_foreign'
            );
            $table->unsignedTinyInteger('skala_nilai_komposisi_proposal')->nullable()->comment('Nilai');

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
        SevimaSchema::dropIfExists('litabmas.penilaian_reviewer_komposisi_proposal');
    }
};
