<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\JenjangPendidikan;

class FakeOrganizationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JenjangPendidikan::firstOrCreate(
            [
                'kode_jenjang' => 'S1',
            ],
            [
                'nama_jenjang' => 'Strata 1',
                'nama_jenjang_en' => 'Bachelor Degree',
                'kode_dikti' => fake()->randomNumber(),
                'apakah_akademik' => true,
                'apakah_pt' => true,
                'apakah_pasca' => false,
                'urutan' => 1,
            ]
        );

        JenjangPendidikan::firstOrCreate(
            [
                'kode_jenjang' => 'S2',
            ],
            [
                'nama_jenjang' => 'Strata 2',
                'nama_jenjang_en' => 'Master Degree',
                'kode_dikti' => fake()->randomNumber(),
                'apakah_akademik' => true,
                'apakah_pt' => true,
                'apakah_pasca' => true,
                'urutan' => 2,
            ]
        );
    }
}

