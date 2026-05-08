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
            $table->text('pmb_deskripsi_unit')->nullable();
            $table->text('pmb_prospek_karir')->nullable();
            $table->text('pmb_bidang_ilmu')->nullable();
            $table->string('pmb_biaya_kuliah')->nullable();
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
                'gelar_singkat_en',
                'pmb_deskripsi_unit',
                'pmb_prospek_karir',
                'pmb_bidang_ilmu',
                'pmb_biaya_kuliah'
            ]);
        });
    }
};
