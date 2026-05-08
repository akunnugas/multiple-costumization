<?php

namespace Modules\PMB\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class PeriodePendaftaranTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        // FIXME: Sementara di comment dulu
        // make data for pmb.registration_periods
        // \Modules\PMB\Models\PeriodePendaftaran::factory()->count(5)->create();
    }
}
