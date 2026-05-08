<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Models\JabatanAkademik;

class JabatanAkademikFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Models\JabatanAkademik::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_jabatan_akademik' => fake()->randomNumber(5),
            'nama_jabatan_akademik' => fake()->word(),
            'kode_emis' => substr(fake()->word(), 0, 3),
            'jenis_jabatan_akademik' => fake()->randomElement([
                JabatanAkademik::DOSEN_AKADEMIK,
                JabatanAkademik::DOSEN_PRAKTISI_INDUSTRI,
                JabatanAkademik::TENAGA_KEPENDIDIKAN,
            ]),
        ];
    }
}
