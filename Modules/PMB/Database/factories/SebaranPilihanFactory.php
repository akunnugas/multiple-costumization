<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\PeriodePendaftaran;

class SebaranPilihanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SebaranPilihan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $options = PeriodePendaftaran::OPTION_AMOUNT_OF_PROGRAMS;

        return [
            'id_sebaran_prodi' => SebaranProdi::factory()->create()->id,
            'pilihan' => $this->faker->randomElement($options),
        ];
    }
}
