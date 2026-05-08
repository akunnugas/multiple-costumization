<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenjangPendidikan;
use Modules\DMS\Models\Dokumen;

class PengisianPanduanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PengisianPanduan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_pengisian_panduan' => substr(fake()->word(), 0, 3),
            'nama_pengisian_panduan' => fake()->word(),
            'nama_singkat' => fake()->word(),
            'id_jenjang_pendidikan' => JenjangPendidikan::factory()->create()->id,
            'kode_level_akses' => fake()->randomElement(['PS', 'PT', 'UPPS']),
            'id_akreditasi_buku' => \Modules\SPMI\Models\AkreditasiBuku::factory(),
            'id_lembaga_akreditasi' => \Modules\Core\Models\LembagaAkreditasi::factory(),
            'id_jenis_standar' => \Modules\SPMI\Models\JenisStandar::factory(),
            'id_dokumen' => Dokumen::factory()->create()->id,
            'tipe_edisi' => fake()->randomElement(['pr', 'se']),
            'deskripsi' => fake()->sentence(),
            'apakah_aktif' => fake()->boolean(),
            'tanggal_edisi' => fake()->date(),
            'tanggal_efektif' => fake()->date(),
            'tanggal_kadaluwarsa' => fake()->date(),
            'apakah_sapto' => fake()->boolean(),
        ];
    }
}
