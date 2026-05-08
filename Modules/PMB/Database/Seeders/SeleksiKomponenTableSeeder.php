<?php

namespace Modules\PMB\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SeleksiKomponenTableSeeder extends Seeder
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
        // \Modules\PMB\Models\SeleksiKomponen::factory()->count(5)->create();
    }
}
