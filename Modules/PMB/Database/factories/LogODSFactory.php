<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LogODSFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\LogODS::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_pendaftar' => \Modules\PMB\Models\Pendaftar::factory()->create()->id,
            'id_aktivitas' => \Modules\PMB\Models\Aktivitas::factory()->create()->id,
            'keterangan_log' => $this->faker->text(),
        ];
    }
}

