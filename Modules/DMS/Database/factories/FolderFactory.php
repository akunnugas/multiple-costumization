<?php

namespace Modules\DMS\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Gate\Models\User;

class FolderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\DMS\Models\Folder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_folder' => fake()->name(),
            'id_pemilik' => User::factory()->create()->id,
            'apakah_hanya_lihat' => false
        ];
    }
}
