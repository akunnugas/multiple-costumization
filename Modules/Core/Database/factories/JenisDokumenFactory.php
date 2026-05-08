<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JenisDokumenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Core\Models\JenisDokumen::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_jenis_dokumen' => $this->faker->unique()->name,
            'kode_jenis_dokumen' => $this->faker->unique()->regexify('[A-Za-z0-9]{10}'),
            'apakah_statis' => false,
        ];
    }
}

