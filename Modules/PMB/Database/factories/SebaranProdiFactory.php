<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\UnitKerja;
use Modules\PMB\Models\PeriodePendaftaran;

class SebaranProdiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SebaranProdi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_periode_pendaftaran' => PeriodePendaftaran::factory()->create()->id,
            'id_unit_kerja' => UnitKerja::factory()->create()->id,
            'daya_tampung' => fake()->numberBetween(1, 300),
            'nilai_minimal' => fake()->numberBetween(1, 100),
            'prefix_nim' => Str::password(5, false, true, false),
            'digit_nim_maksimal' => fake()->numberBetween(5, 20),
        ];
    }
}
