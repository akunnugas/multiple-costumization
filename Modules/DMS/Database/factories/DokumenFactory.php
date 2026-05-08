<?php

namespace Modules\DMS\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Folder;
use Modules\DMS\Models\Dokumen;
use Illuminate\Support\Str;

class DokumenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\DMS\Models\Dokumen::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $moduleCode = fake()->word();

        return [
            'id_folder' => Folder::factory()->create()->id,
            'nama_folder' => fake()->name(),
            'slug' => Str::uuid(),
            'ukuran' => fake()->randomNumber(),
            'kode_modul' => $moduleCode,
            'catatan' => fake()->sentence(),
            'visibilitas' => fake()->randomElement([
                Dokumen::VISIBILITY_PRIVATE,
                Dokumen::VISIBILITY_PUBLIC
            ]),
            'versi_terbaru' => fake()->randomNumber(1),
            'alamat_versi_terbaru' => fake()->url(),
            'extension_versi_terbaru' => fake()->randomElement([
                'pdf',
                'doc',
                'png',
                'jpg',
                'jpeg'
            ]),
        ];
    }
}
