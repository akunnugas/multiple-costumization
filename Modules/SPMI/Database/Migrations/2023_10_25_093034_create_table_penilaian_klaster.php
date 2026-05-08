<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\PenilaianKlaster;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.penilaian_klaster', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_klaster', 5);
            $table->string('nama_klaster');

            $table->logs(true);
        });

        // Defaultkan kluster penilaian data
        PenilaianKlaster::create([
            'kode_klaster' => 'IN',
            'nama_klaster' => 'Input',
        ]);
        PenilaianKlaster::create([
            'kode_klaster' => 'OO',
            'nama_klaster' => 'Output & Outcome',
        ]);
        PenilaianKlaster::create([
            'kode_klaster' => 'PR',
            'nama_klaster' => 'Proses',
        ]);
        PenilaianKlaster::create([
            'kode_klaster' => 'TK',
            'nama_klaster' => 'Mutu Kepemimpinan dan Kinerja Tata Kelola',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.penilaian_klaster');
    }
};
