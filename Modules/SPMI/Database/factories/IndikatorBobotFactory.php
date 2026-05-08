<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\AuditPeriode;

class IndikatorBobotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorBobot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'jenis_indikator_bobot' => fake()->unique()->text(10),
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'nama_kategori_indikator' => fake()->text(255),
            'persentase' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
