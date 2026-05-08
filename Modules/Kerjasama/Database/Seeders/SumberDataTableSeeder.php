<?php

namespace Modules\Kerjasama\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Kerjasama\Models\SumberDana;

class SumberDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $dataSumberData = [
            "Pemerintah",
            "Swasta",
            "Donasi",
            "Hibah",
            "Investasi",
            "Crowdfunding",
            "Pinjaman",
            "Lembaga Internasional",
            "Program Kerjasama",
            "Sponsorship"
        ];

        foreach ($dataSumberData as $data) {
            SumberDana::updateOrCreate([
                "sumber_dana" => $data
            ]);
        }
    }
}
