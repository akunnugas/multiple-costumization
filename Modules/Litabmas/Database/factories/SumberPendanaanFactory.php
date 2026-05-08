<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Helpers\Date;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;

class SumberPendanaanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SumberPendanaan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categories = array_keys(SumberPendanaan::CATEGORIES);
        $years = Date::getYearOptions(yearRange: 4);

        return [
            'id_periode_pendanaan' => PeriodePendanaan::factory()->create(['year' => fake()->randomElement($years)])->id,
            'nama_sumber_pendanaan' => 'Sumber Pendanaan ' . fake()->randomNumber(),
            'id_unit_kerja' => UnitKerja::factory()->create()->id,
            'kategori_sumber_pendanaan' => fake()->randomElement($categories),
            'total_pendanaan' => fake()->numberBetween(1000000, 100000000),
            'mata_uang' => 'IDR',
        ];
    }
}
