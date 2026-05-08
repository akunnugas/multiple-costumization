<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.penilaian_reviewer_output', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(null, 'id_pengajuan_pendanaan_reviewer',
                foreignName: 'litabmas_pro_id_pengajuan_pendanaan_reviewer',
                indexName: 'pro_id_pengajuan_pendanaan_reviewer_foreign',
                table: 'litabmas.pengajuan_pendanaan_reviewer'
            );
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaanOutputPenelitian::class, 'id_pengajuan_pendanaan_output_penelitian',
                foreignName: 'litabmas_pro_id_pengajuan_pendanaan_output_penelitian', indexName: 'pro_id_pengajuan_pendanaan_output_penelitian_foreign'
            );
            $table->string('status_penilaian_output', 25)->nullable()->comment('Status');
            $table->text('feedback_output')->nullable()->comment('Feedback');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.penilaian_reviewer_output');
    }
};
