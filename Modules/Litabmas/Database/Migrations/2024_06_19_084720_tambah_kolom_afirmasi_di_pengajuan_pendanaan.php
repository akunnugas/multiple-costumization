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
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->boolean('apakah_afirmasi')->default(false)->comment('Afirmasi');
            $table->string('catatan_lolos_pendanaan')->nullable()->comment('Catatan Lolos Pendanaan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_afirmasi');
            $table->dropColumn('catatan_lolos_pendanaan');
        });
    }
};
