<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\SeleksiKomponen;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\PeriodePendaftaran;

class SeleksiKomposisiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SeleksiKomposisi::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_periode_pendaftaran' => PeriodePendaftaran::factory()->create()->id,
            'id_seleksi_jenis' => SeleksiJenis::factory()->create()->id,
            'id_seleksi_komponen' => SeleksiKomponen::factory()->create()->id,
            'persentase_komposisi' => $this->faker->numberBetween(1, 100),
        ];
    }
}
