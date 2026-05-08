<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Biodata;
use Modules\Litabmas\Models\DosenEksternal;

class DosenEksternalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\DosenEksternal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = DosenEksternal::STATUS;

        return [
            'id_biodata' => Biodata::factory()->create()->id,
            'id_pegawai' => fake()->text(),
            'id_biodata_pengusul' => null,
            'status_usulan' => fake()->randomElement(array_keys($status)),
        ];
    }
}
