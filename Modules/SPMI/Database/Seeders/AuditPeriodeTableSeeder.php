<?php

namespace Modules\SPMI\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\IndikatorBobot;

class AuditPeriodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $now = now();
        $year = $now->year;
        $years = range($year - 5, $year);
        
        $periods = []; 
        foreach ($years as $year) {
            $periods[] = AuditPeriode::create([
                'tahun_audit' => $year,
            ]);
        }

        // Pembuatan 2 indikator untuk setiap periode audit
        foreach ($periods as $period) {
            $period->indikatorBobot()->create([
                'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKU,
                'nama_kategori_indikator' => 'Indikator Kinerja Utama',
                'persentase' => 0.00,
            ]);
            $period->indikatorBobot()->create([
                'jenis_indikator_bobot' => IndikatorBobot::TYPE_IKT,
                'nama_kategori_indikator' => 'Indikator Kinerja Tambahan',
                'persentase' => 0.00,
            ]);
        }
    }
}
