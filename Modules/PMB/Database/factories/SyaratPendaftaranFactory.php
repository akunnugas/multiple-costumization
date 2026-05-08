<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\Syarat;
use Modules\PMB\Models\PeriodePendaftaran;
use Modules\PMB\Models\SyaratJenis;

class SyaratPendaftaranFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SyaratPendaftaran::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_periode_pendaftaran' => PeriodePendaftaran::factory()->create()->id,
            'id_seleksi_syarat' => Syarat::factory()->create()->id,
            'id_jenis_syarat' => SyaratJenis::factory()->create()->id,
            'apakah_wajib' => fake()->boolean(),
            'apakah_upload' => fake()->boolean(),
            'jumlah_dokumen' => fake()->randomNumber(5),
        ];
    }
}
