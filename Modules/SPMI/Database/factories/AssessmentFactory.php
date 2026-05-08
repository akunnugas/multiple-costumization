<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;

class AssessmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianAudit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'accreditation_agency_id' => LembagaAkreditasi::factory()->create()->id,
            'assessment_guide_id' => PenilaianPanduan::factory()->create()->id,
            'id_unit' => UnitKerja::factory()->create()->id,
            'audit_schedule_id' => JadwalAudit::factory()->create()->id,
            'lead_auditor_id' => fake()->randomNumber(2),
            'is_self_assessment' => fake()->boolean(),
            'note' => fake()->text(),
            'final_score' => 0,
            'is_finalized' => fake()->boolean(),
            'total_finding' => fake()->randomNumber(2),
        ];
    }
}
