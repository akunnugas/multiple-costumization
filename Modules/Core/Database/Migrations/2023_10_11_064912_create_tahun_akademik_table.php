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
        SevimaSchema::create('core.tahun_akademik', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('tahun');
            $table->logs(true);
        });

        SevimaSchema::table('core.tahun_akademik', function (SevimaBlueprint $table) {
            $table->uniqueIndex('tahun', true);
        });

        // create data untuk 5 tahun kedepan
        $now = date('Y');
        for ($i = 0; $i <= 4; $i++) {
            $year = $now + $i;
            DB::table('core.tahun_akademik')->insert([
                'tahun' => $year . '/' . ($year + 1),
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
        SevimaSchema::dropIfExists('core.tahun_akademik');
    }
};
