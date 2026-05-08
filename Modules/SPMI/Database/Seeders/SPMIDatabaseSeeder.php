<?php

namespace Modules\SPMI\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SPMIDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
        $this->call(AuditPeriodeTableSeeder::class);  // Bobot Indikator & Periode Audit
        // $this->call(PengisianPanduanSeeder::class); // Filling Guide
        $this->call(SPMIRankTableSeeder::class); // Butir LK
        // $this->call(IndicataorSelfEvaluationSeeder::class); // Butir LED
    }
}
