<?php

namespace Modules\PMB\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SeleksiJenisTableSeeder extends Seeder
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
        // make data for pmb.assessment_types
        // \Modules\PMB\Models\SeleksiJenis::factory()->count(5)->create();
    }
}
