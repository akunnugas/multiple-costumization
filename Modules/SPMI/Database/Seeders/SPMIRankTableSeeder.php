<?php

namespace Modules\SPMI\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SpmiPeringkat;

class SPMIRankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SpmiPeringkat::firstOrCreate([
            'id_audit_periode' => AuditPeriode::findNowYearPeriod()->id,
            'kode_spmi_peringkat' => 'PM001',
            'nama_spmi_peringkat' => 'Excellent Leader',
            'skor_minimal' => 95.01,
            'skor_maksimal' => 100.00,
            'deskripsi' => 'Peringkat 1',
        ]);

        SpmiPeringkat::firstOrCreate([
            'id_audit_periode' => AuditPeriode::findNowYearPeriod()->id,
            'kode_spmi_peringkat' => 'PM002',
            'nama_spmi_peringkat' => 'Emerging Leader',
            'skor_minimal' => 80.01,
            'skor_maksimal' => 95.00,
            'deskripsi' => 'Peringkat 2',
        ]);

        SpmiPeringkat::firstOrCreate([
            'id_audit_periode' => AuditPeriode::findNowYearPeriod()->id,
            'kode_spmi_peringkat' => 'PM003',
            'nama_spmi_peringkat' => 'Good Performance',
            'skor_minimal' => 60.01,
            'skor_maksimal' => 80.00,
            'deskripsi' => 'Peringkat 3',
        ]);

        SpmiPeringkat::firstOrCreate([
            'id_audit_periode' => AuditPeriode::findNowYearPeriod()->id,
            'kode_spmi_peringkat' => 'PM004',
            'nama_spmi_peringkat' => 'Early Improvement',
            'skor_minimal' => 40.01,
            'skor_maksimal' => 60.00,
            'deskripsi' => 'Peringkat 4',
        ]);

        SpmiPeringkat::firstOrCreate([
            'id_audit_periode' => AuditPeriode::findNowYearPeriod()->id,
            'kode_spmi_peringkat' => 'PM005',
            'nama_spmi_peringkat' => 'Early Result',
            'skor_minimal' => 20.01,
            'skor_maksimal' => 40.00,
            'deskripsi' => 'Peringkat 5',
        ]);

        SpmiPeringkat::firstOrCreate([
            'id_audit_periode' => AuditPeriode::findNowYearPeriod()->id,
            'kode_spmi_peringkat' => 'PM006',
            'nama_spmi_peringkat' => 'Early Development',
            'skor_minimal' => 0.00,
            'skor_maksimal' => 20.00,
            'deskripsi' => 'Peringkat 6',
        ]);
    }
}
