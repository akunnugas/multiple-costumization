<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JurusanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\Jurusan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_jurusan' => Str::password(20, false, true, false),
            'nama_jurusan' => fake()->word(),
            'id_jenjang_pendidikan' => \Modules\Core\Models\JenjangPendidikan::factory()->create()->id,
        ];
    }
}
