<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\TahunAkademik;

class PeriodeAkademikFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\PeriodeAkademik::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $timestampTzStart = date('Y-m-d');
        $timestampTzEnd = date('Y-m-d', strtotime('+1 Year', strtotime($timestampTzStart)));
        return [
            'kode_periode' => Str::password(10, false, true, false),
            'nama_periode' => fake()->word(),
            'id_tahun_akademik' => TahunAkademik::first()->id,
            'waktu_mulai_periode' => $timestampTzStart,
            'waktu_selesai_periode' => $timestampTzEnd,
        ];
    }
}
