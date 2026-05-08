<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\JenisPerguruanTinggi;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\JenisStandar;

class PenilaianPanduanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\PenilaianPanduan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_penilaian_panduan' => substr(fake()->word(), 0, 3),
            'nama_penilaian_panduan' => fake()->word(),
            'nama_singkat' => substr(fake()->word(), 0, 3),
            'id_laporan_kinerja' => PengisianPanduan::factory()->create()->id,
            'id_panduan_evaluasi_diri' => PengisianPanduan::factory()->create()->id,
            'tanggal_edisi' => fake()->date(),
            'id_jenjang_pendidikan' => JenjangPendidikan::factory()->create()->id,
            'id_jenis_perguruan_tinggi' => JenisPerguruanTinggi::factory()->create()->id,
            'id_jenis_standar' => JenisStandar::factory()->create()->id,
            'deskripsi' => fake()->sentence(),
            'apakah_ptn' => fake()->boolean(),
            'apakah_aktif' => fake()->boolean(),
            'dapat_lihat_skor_akhir' => fake()->boolean(),
            'id_dokumen' => Dokumen::factory()->create()->id,
        ];
    }
}
