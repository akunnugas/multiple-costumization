<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\AuditPeriode;

class SkAuditorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\SkAuditor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'nomor_sk' => fake()->text(255),
            'tanggal_diterbitkan' => fake()->text(),
            'tanggal_awal_berlaku' => fake()->text(),
            'tanggal_akhir_berlaku' => fake()->text(),
            'id_dokumen' => Dokumen::factory()->create()->id,
        ];
    }
}
