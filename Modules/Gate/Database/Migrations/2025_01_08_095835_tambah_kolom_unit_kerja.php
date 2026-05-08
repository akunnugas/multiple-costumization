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
        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('gelar')->nullable();
            $table->string('gelar_en')->nullable();
            $table->string('gelar_singkat')->nullable();
            $table->string('gelar_singkat_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.unit_kerja', function (SevimaBlueprint $table) {
            $table->dropColumn([
                'visi',
                'misi',
                'alamat',
                'telepon',
                'website',
                'email',
                'gelar',
                'gelar_en',
                'gelar_singkat',
                'gelar_singkat_en'
            ]);
        });
    }
};
