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
        JenjangPendidikan::where('kode_jenjang', 'UNP')->update([
            'kode_jenjang' => 'UNA',
            'nama_jenjang' => 'Unit Non Akademik',
            'nama_jenjang_en' => 'Non Academic Unit',
        ]);
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        JenjangPendidikan::where('kode_jenjang', 'UNA')->update([
            'kode_jenjang' => 'UNP',
            'nama_jenjang' => 'Unit Non Prodi',
            'nama_jenjang_en' => 'Non Study Program Unit',
        ]);
    }
};
