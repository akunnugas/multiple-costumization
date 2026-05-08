<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DMS\Models\Dokumen;
use Modules\PMB\Models\Pengumuman;

class PengumumanFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\PengumumanFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_file' => Dokumen::factory()->create()->id,
            'id_pengumuman' => Pengumuman::factory()->create()->id,
        ];
    }
}
