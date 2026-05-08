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
        SevimaSchema::table('core.biodata', function (SevimaBlueprint $table) {
            // change column ref_key_siakad to ref_key_pegawai
            $table->renameColumn('ref_key_siakad', 'ref_key_pegawai');
            $table->string('ref_key_mahasiswa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.biodata', function (SevimaBlueprint $table) {
            $table->renameColumn('ref_key_pegawai', 'ref_key_siakad');
            $table->dropColumn('ref_key_mahasiswa');
        });
    }
};
