<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IndikatorCellFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\IndikatorCell::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'column_to' => fake()->randomNumber(5),
            'row_to' => fake()->randomNumber(5),
            'kategori_cell' => fake()->text(255),
            'jenis_cell' => fake()->randomElement(['L','D','J','A','R','B','E']),
            'posisi_label' => fake()->text(255),
            'name' => fake()->name(),
            'colspan' => fake()->text(255),
            'rowspan' => fake()->text(255),
            'dapat_dilihat' => fake()->boolean(),
        ];
    }
}
