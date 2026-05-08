<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\AuditPeriode;

class SuratTugasAuditorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\SuratTugasAuditor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'nomor_surat_tugas' => fake()->text(255),
            'tanggal_surat_tugas' => fake()->text(),
            'tanggal_mulai' => fake()->text(),
            'tanggal_selesai' => fake()->text(),
            'id_dokumen' => Dokumen::factory()->create()->id,
        ];
    }
}
