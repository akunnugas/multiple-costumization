<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // drop old columns
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_proposal_valid');
            $table->dropColumn('waktu_validasi_proposal');
            $table->dropColumn('proposal_valid_oleh');
        });

        // add column
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_dokumen_lengkap')->nullable();
            $table->timestampTz('waktu_validasi_dokumen')->nullable();
            $table->boolean('apakah_similarity_ai_memenuhi')->nullable();
            $table->timestampTz('waktu_validasi_similarity_ai')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop new columns
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_dokumen_lengkap');
            $table->dropColumn('waktu_validasi_dokumen');
            $table->dropColumn('apakah_similarity_ai_memenuhi');
            $table->dropColumn('waktu_validasi_similarity_ai');
        });

        // add old columns
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_proposal_valid')->default(false)
                ->comment('Apakah dokumen proposal valid?');
            $table->timestampTz('waktu_validasi_proposal')->nullable()
                ->comment('Waktu validasi dokumen proposal');
            $table->foreignIdTo(User::class,'proposal_valid_oleh', true);
        });
    }
};
