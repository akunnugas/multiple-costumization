<?php

namespace Modules\PMB\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SebaranProdiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // FIXME: Sementara di comment dulu
        // make data for pmb.program_distributions
        // \Modules\PMB\Models\SebaranProdi::factory()->count(5)->create();
    }
}
