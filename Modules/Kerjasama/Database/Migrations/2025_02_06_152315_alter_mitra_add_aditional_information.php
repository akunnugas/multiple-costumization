<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Wilayah;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.mitra', function (SevimaBlueprint $table) {
            $table->string('kode_mitra')->nullable();
            $table->string('npwp_mitra')->nullable();
            $table->string('kode_pos')->nullable();

            $table->foreignIdTo(Wilayah::class, 'id_kecamatan', true);
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.mitra', function (SevimaBlueprint $table) {
            $table->dropColumn('kode_mitra')->nullable();
            $table->dropColumn('npwp_mitra')->nullable();
            $table->dropColumn('kode_pos')->nullable();

            $table->dropForeignIdFor(Wilayah::class, 'id_kecamatan');
        });
    }
};
