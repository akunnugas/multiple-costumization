<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PekerjaanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Pekerjaan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $code = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];

        return [
            'nama_pekerjaan' => $this->faker->unique()->jobTitle,
            'kode_emis' => $this->faker->randomElement($code),
            'kode_emis_siswa' => $this->faker->randomElement($code),
            'kode_emis_lulusan' => $this->faker->randomElement($code),
            'kode_sister' => $this->faker->randomNumber(5),
        ];
    }
}
