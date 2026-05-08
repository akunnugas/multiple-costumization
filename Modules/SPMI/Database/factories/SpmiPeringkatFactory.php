<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\AuditPeriode;

class SpmiPeringkatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\SpmiPeringkat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'code' => fake()->text(10),
            'name' => fake()->name(),
            'min_score' => fake()->randomFloat(2, 1, 100),
            'max_score' => fake()->randomFloat(2, 1, 100),
            'description' => fake()->text(),
        ];
    }
}
