<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\PeriodeAkademik;
use Modules\Core\Models\Biodata;
use Modules\Gate\Models\User;
use Modules\PMB\Models\PeriodePendaftaran;

class PendaftarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\Pendaftar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kode_pendaftar' => Str::password(10, false, true, false),
            'id_biodata' => Biodata::factory()->create()->id,
            'id_periode_pendaftaran' => PeriodePendaftaran::factory()->create()->id,
            'id_periode_akademik' => PeriodeAkademik::factory()->create()->id,
            // 'id_mahasiswa',
            'sumber_data' => fake()->word(),
            'status_lulus' => fake()->randomElement(
                $this->model::QUALIFIED_STATUS
            ),
            'id_prodi_lulus' => UnitKerja::factory()->create()->id,
            'direkomendasikan_oleh' => User::factory()->create()->id,
            'utm_source' => fake()->word(),
            'apakah_import_nim' => fake()->boolean(),
            'waktu_registrasi' => fake()->dateTime(),
            'tanggal_daftar_ulang' => fake()->date(),
            'waktu_finalisasi' => fake()->dateTime(),
            'waktu_aktif' => fake()->dateTime(),
        ];
    }
}
