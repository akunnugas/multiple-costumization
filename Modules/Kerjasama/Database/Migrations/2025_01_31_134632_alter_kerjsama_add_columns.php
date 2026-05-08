<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\SumberDana;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->foreignIdTo(BentukKegiatan::class, 'id_bentuk_kegiatan', true);
            $table->foreignIdTo(SumberDana::class, 'id_sumber_dana', true);
            $table->float('anggaran')->nullable();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.kerjasama', function (SevimaBlueprint $table) {
            $table->dropColumn('anggaran');
            $table->dropColumn('id_sumber_dana');
            $table->dropColumn('id_bentuk_kegiatan');
        });
    }
};
