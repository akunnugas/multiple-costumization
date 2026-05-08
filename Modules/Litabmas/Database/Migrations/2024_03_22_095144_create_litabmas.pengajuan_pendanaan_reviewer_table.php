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
        SevimaSchema::create('litabmas.pengajuan_pendanaan_reviewer', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Litabmas\Models\PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->foreignIdTo(\Modules\Gate\Models\User::class, 'id_user');
            $table->unsignedTinyInteger('reviewer_ke')->comment('Reviewer ke');
            $table->decimal('total_penilaian_komposisi_proposal', 5, 2, true)
                ->comment('Total penilaian komposisi proposal');
            $table->decimal('total_penilaian_presentasi_proposal', 5, 2, true)
                ->comment('Total penilaian presentasi proposal');
            $table->text('komentar_umum_penilaian_output')->nullable()->comment('Komentar umum penilaian output');
            $table->string('status_presentasi_progres', 25)->nullable()->comment('Status');
            $table->text('komentar_umum_presentasi_progres')->nullable()->comment('Komentar umum presentasi progres');

            $table->logs();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_reviewer');
    }
};
