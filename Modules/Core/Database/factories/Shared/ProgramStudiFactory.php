<?php

namespace Modules\Core\Database\factories\Shared;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Shared\PerguruanTinggi;

class ProgramStudiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Shared\ProgramStudi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_prodi' => fake()->text(255),
            'nama_prodi' => fake()->name(),
            'alamat_prodi' => fake()->text(255),
            'telepon_prodi' => fake()->text(255),
            'kode_sister' => fake()->text(255),
            'id_perguruan_tinggi' => PerguruanTinggi::factory()->create()->id,
        ];
    }
}
