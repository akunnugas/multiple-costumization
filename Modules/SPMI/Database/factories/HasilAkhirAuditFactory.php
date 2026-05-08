<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\SpmiPeringkat;

class HasilAkhirAuditFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\HasilAkhirAudit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'id_unit' => UnitKerja::factory()->create()->id,
            'id_penilaian_audit' => PenilaianAudit::factory()->create()->id,
            'nilai_iku' => fake()->randomFloat(2, 1, 100),
            'nilai_ikt' => fake()->randomFloat(2, 1, 100),
            'nilai_akhir' => fake()->randomFloat(2, 1, 100),
            'nilai_akhir_auditee' => fake()->randomFloat(2, 1, 100),
            'persentase_nilai_akhir' => fake()->randomFloat(2, 1, 100),
            'id_spmi_peringkat' => SpmiPeringkat::factory()->create()->id,
            'id_akreditasi_peringkat' => fake()->randomElement(AkreditasiPeringkat::pluck('id')->toArray()),
        ];
    }
}
