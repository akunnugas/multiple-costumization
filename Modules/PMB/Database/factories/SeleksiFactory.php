<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\SebaranProdi;

class SeleksiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Seleksi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $baseTimestamp = date('Y-m-d H:i:sO');
        $startedAt = $baseTimestamp;
        $endedAt = date('Y-m-d H:i:sO', strtotime('+1 Months', strtotime($baseTimestamp)));
        return [
            'id_sebaran_prodi' => SebaranProdi::factory()->create()->id,
            'id_jenis_seleksi' => SeleksiJenis::factory()->create()->id,
            'urutan_seleksi' => fake()->numberBetween(1, 100),
            'persentase_nilai' => fake()->numberBetween(1, 100),
            'waktu_mulai' => $startedAt,
            'waktu_selesai' => $endedAt,
        ];
    }
}
