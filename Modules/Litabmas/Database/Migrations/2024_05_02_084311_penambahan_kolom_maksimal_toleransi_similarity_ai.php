<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.sumber_pendanaan', function (SevimaBlueprint $table) {
            $table->decimal('maksimal_toleransi_similarity', 5, 2)->nullable()->comment('Maksimal Nilai Similarity');
            $table->decimal('maksimal_toleransi_ai', 5, 2)->nullable()->comment('Maksimal Nilai AI');
            $table->unsignedTinyInteger('maksimal_pendaftar')->nullable()->comment('Maksimal Pendaftar');
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.sumber_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('maksimal_toleransi_similarity');
            $table->dropColumn('maksimal_toleransi_ai');
            $table->dropColumn('maksimal_pendaftar');
        });
    }
};
