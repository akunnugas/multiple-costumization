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
        SevimaSchema::table('spmi.audit_temuan', function (SevimaBlueprint $table) {
            $table->text('akar_masalah')
                ->nullable()
                ->comment('Akar Masalah');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('spmi.audit_temuan', function (SevimaBlueprint $table) {
            $table->dropColumn('akar_masalah');
        });
    }
};
