<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\PengajuanPendanaanIsianProposal;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('litabmas.penilaian_reviewer_isian_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(null, 'id_pengajuan_pendanaan_reviewer',
                foreignName: 'litabmas_prib_id_pengajuan_pendanaan_reviewer',
                indexName: 'prib_id_pengajuan_pendanaan_reviewer_foreign',
                table: 'litabmas.pengajuan_pendanaan_reviewer'
            );
            $table->foreignIdTo(PengajuanPendanaanIsianProposal::class, 'id_pengajuan_pendanaan_isian_proposal',
                foreignName: 'litabmas_prib_id_pengajuan_pendanaan_isian_proposal', indexName: 'prib_id_pengajuan_pendanaan_isian_proposal_foreign'
            );
            $table->text('feedback_isian_proposal')->nullable()->comment('Feedback');

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
        SevimaSchema::dropIfExists('litabmas.penilaian_reviewer_isian_proposal');
    }
};
