<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\SumberPendanaan;

class KlasterPendanaanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = KlasterPendanaan::class;
    private static $order = 1;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_sumber_pendanaan' => SumberPendanaan::factory()->create()->id,
            'nama_klaster' => 'Klaster ' . self::$order++,
            'maksimal_anggaran' => $this->faker->numberBetween(1000000, 100000000),
            'maksimal_anggota' => $this->faker->numberBetween(1, 10),
        ];
    }
}
