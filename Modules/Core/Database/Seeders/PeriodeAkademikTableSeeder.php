<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class PeriodeAkademikTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        // make data for core.periods
        \Modules\Core\Models\PeriodeAkademik::factory()->count(5)->create();
    }
}
