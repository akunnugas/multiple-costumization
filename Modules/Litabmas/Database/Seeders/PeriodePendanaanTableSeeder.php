<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Litabmas\Models\PeriodePendanaan;

class PeriodePendanaanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //fake year 5 last year
        //current year and next 1 is active period
        $year = date('Y') - 2;

        for ($i = 0; $i < 3; $i++) {
            $startDate = $year . '-01-01';
            $endDate = $year . '-12-29';

            PeriodePendanaan::updateOrCreate([
                'tahun' => $year,
                'tanggal_mulai' => $startDate,
                'tanggal_akhir' => $endDate,
                'maksimal_ketua_mendaftar' => 1,
                'maksimal_anggota_mendaftar' => 5,
            ]);

            $year++;
        }
    }
}
