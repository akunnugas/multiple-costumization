<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\HR\Models\Echelon;
use Modules\HR\Models\PositionLevel;
use Modules\HR\Models\StructuralPositionType;

class StructuralPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Models\StructuralPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'parent_id' => null,
            'organization_id' => UnitKerja::factory()->create()->id,
            'min_position_level_id' => PositionLevel::factory()->create()->id,
            'max_position_level_id' => PositionLevel::factory()->create()->id,
            'structural_position_type_id' => StructuralPositionType::factory()->create()->id,
            'echelon_id' => Echelon::factory()->create()->id,
            'name' => fake()->name(),
            'code' => fake()->randomNumber(5),
            'email' => fake()->email(),
            'description' => fake()->sentence(),
            'is_active' => true,
            'is_leader' => fake()->randomElement([true, false]),
            'abbreviation' => fake()->word(),
        ];
    }
}

