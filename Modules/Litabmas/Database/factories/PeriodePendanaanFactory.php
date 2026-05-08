<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodePendanaanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\PeriodePendanaan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $startDate = fake()->date();

        return [
            'tahun' => fake()->year(),
            'tanggal_mulai' => $startDate,
            'tanggal_akhir' => fake()->dateTimeBetween($startDate, '+1 years'),
        ];
    }
}
