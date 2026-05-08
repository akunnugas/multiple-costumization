<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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
        SevimaSchema::create('tahun_akademik', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_tahun');
            $table->logs(true);
        });

        SevimaSchema::table('tahun_akademik', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_tahun', true);
        });

        // create data academic year untuk 5 tahun kedepan
        $now = date('Y');
        for ($i = 0; $i <= 4; $i++) {
            $year = $now + $i;
            DB::connection('shared')->table('tahun_akademik')->insert([
                'nama_tahun' => $year . '/' . ($year + 1),
                'waktu_dibuat' => now(),
                'waktu_diubah' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('tahun_akademik');
    }
};
