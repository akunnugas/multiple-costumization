<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SPMI\Models\AkreditasiStandar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add data to table akreditasi_standar
        AkreditasiStandar::insert([[
                'kode_standar' => 'C.1',
                'nama_standar' => 'Visi, Misi, Tujuan dan Strategi',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.2',
                'nama_standar' => 'Tata Pamong, Tata Kelola dan Kerjasama',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.3',
                'nama_standar' => 'Mahasiswa',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.4',
                'nama_standar' => 'Sumber Daya Manusia',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.5',
                'nama_standar' => 'Keuangan, Sarana dan Prasarana',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.6',
                'nama_standar' => 'Pendidikan',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.7',
                'nama_standar' => 'Penelitian',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.8',
                'nama_standar' => 'Pengabdian kepada Masyarakat',
                'id_jenis_standar' => 1,
            ],
            [
                'kode_standar' => 'C.9',
                'nama_standar' => 'Luaran dan Capaian Tridharma',
                'id_jenis_standar' => 1,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // delete data from table akreditasi_standar
        AkreditasiStandar::truncate();
    }
};
