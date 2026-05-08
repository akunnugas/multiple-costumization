<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SeleksiKomponenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SeleksiKomponen::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_komponen'  => $this->faker->unique()->regexify('[A-Za-z0-9]{10}'),
            'nama_komponen'  => 'Test ' . $this->faker->unique()->regexify('[A-Za-z0-9]{10}')
        ];
    }
}
