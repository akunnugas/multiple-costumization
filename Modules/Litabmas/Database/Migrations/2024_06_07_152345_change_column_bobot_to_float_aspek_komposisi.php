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
        // AspekPenilaianKomposisiProposal
        SevimaSchema::table('litabmas.aspek_penilaian_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->float('bobot_komposisi_proposal')->change();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // AspekPenilaianKomposisiProposal
        SevimaSchema::table('litabmas.aspek_penilaian_komposisi_proposal', function (SevimaBlueprint $table) {
            $table->integer('bobot_komposisi_proposal')->change();
        });
    }
};
