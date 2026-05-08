<?php

namespace Modules\Kerjasama\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Kerjasama\Models\BentukKegiatan;

class KerjasamaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(BentukKegiatanTableSeeder::class);
        $this->call(KriteriaMitraTableSeeder::class);
        $this->call(SumberDataTableSeeder::class);
        $this->call(JenisDokumenTableSeeder::class);
    }
}
