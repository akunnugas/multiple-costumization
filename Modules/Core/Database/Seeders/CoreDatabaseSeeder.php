<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Seeders\BloodTypesTableSeeder;
use Modules\Core\Database\Seeders\JenjangPendidikanTableSeeder;
use Modules\Core\Database\Seeders\SukuTableSeeder;
use Modules\Core\Database\Seeders\PekerjaanTableSeeder;
use Modules\Core\Database\Seeders\PeriodeAkademikTableSeeder;
use Modules\Core\Database\Seeders\AgamaTableSeeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");

        //Referensi
        $this->call(BloodTypesTableSeeder::class); // Gol Darah
        // $this->call(DegreesTableSeeder::class); // Jenjang Pendidikan
        $this->call(SukuTableSeeder::class); // Suku
        $this->call(PekerjaanTableSeeder::class); // Pekerjaan
        $this->call(PeriodeAkademikTableSeeder::class); // Periode Akademik
        $this->call(AgamaTableSeeder::class); // Agama
        $this->call(WilayahTableSeeder::class); // Wilayah
        $this->call(FakeOrganizationsTableSeeder::class); // Organisasi
    }
}
