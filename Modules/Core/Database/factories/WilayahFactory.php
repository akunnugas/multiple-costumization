<?php

namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Wilayah;

class WilayahFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wilayah::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $keyRegionLevels = array_keys(Wilayah::LEVELS);
        $regionLevel = fake()->randomElement($keyRegionLevels);
        $parentIds = Wilayah::where('level_wilayah', '<', $regionLevel)->pluck('id')->toArray();
        $parentId = fake()->randomElement($parentIds);

        return [
            'nama_wilayah' => fake()->word(),
            'id_parent' => $parentId,
            'kode_wilayah' => fake()->unique()->randomNumber(8),
            'level_wilayah' => $regionLevel,
            'kode_dikti' => fake()->unique()->randomNumber(8),
            'kode_bps' => fake()->unique()->randomNumber(8),
            'kode_dagri' => fake()->unique()->randomNumber(8),
        ];
    }
}
