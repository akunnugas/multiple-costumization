<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\JenisDokumen;
use Modules\Kerjasama\Services\JenisDokumenManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('kerjasama.jenis_dokumen', function(SevimaBlueprint $table) {
            $table->boolean('isian_default')->default(false);
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.jenis_dokumen', function(SevimaBlueprint $table) {
            $table->dropColumn('isian_default');
        });
    }
};
