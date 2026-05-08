<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\JenjangPendidikan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('core.jenjang_pendidikan', function (SevimaBlueprint $table) {
            if (!SevimaSchema::hasColumn('core.jenjang_pendidikan', 'apakah_data_default')) {
                $table->boolean('apakah_data_default')->default(false);
            }
        });

        JenjangPendidikan::create([
            'nama_jenjang' => 'Unit Non Prodi',
            'nama_jenjang_en' => 'Non Study Program Unit',
            'kode_jenjang' => 'UNP',
            'kode_dikti' => null,
            'apakah_akademik' => false,
            'apakah_pt' => true,
            'apakah_pasca' => false,
            'kode_emis' => null,
            'kode_emis_pasca' => null,
            'kode_emis_dosen' => null,
            'urutan' => 0,
            'ref_key_siakad' => null
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('core.jenjang_pendidikan', function (SevimaBlueprint $table) {
            $table->dropColumn('apakah_data_default');
        });

        JenjangPendidikan::where('kode_jenjang', 'UNP')->delete();
    }
};
