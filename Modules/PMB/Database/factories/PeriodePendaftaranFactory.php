<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\SistemKuliah;
use Modules\Core\Models\PeriodeAkademik;
use Modules\Core\Models\JenisPendaftaran;
use Modules\PMB\Models\Gelombang;
use Modules\PMB\Models\JalurPendaftaran;
use Modules\PMB\Models\PeriodePendaftaran;

class PeriodePendaftaranFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\PeriodePendaftaran::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $baseTimestamp = date('Y-m-d\TH:i');
        $openedAt = $baseTimestamp;
        $closedAt = date('Y-m-d\TH:i', strtotime('+3 Months', strtotime($baseTimestamp)));
        $lastGraduationYear = date('Y', strtotime('-9 Years', strtotime($baseTimestamp)));
        $startBirthDate = date('Y-m-d', strtotime('-17 Years', strtotime($baseTimestamp)));
        $lastBirthDate = date('Y-m-d', strtotime('-19 Years', strtotime($baseTimestamp)));

        return [
            'kode_periode' => fake()->unique()->word(),
            'nama_periode' => fake()->sentence(),
            'id_periode_akademik' => PeriodeAkademik::factory()->create()->id,
            'id_gelombang' => Gelombang::factory()->create()->id,
            'id_jalur_pendaftaran' => JalurPendaftaran::factory()->create()->id,
            'id_sistem_kuliah' => SistemKuliah::factory()->create()->id,
            'id_jenis_pendaftaran' => JenisPendaftaran::where('kode_jenis_pendaftaran', JenisPendaftaran::CODE_PDB)->first()->id,
            'keterangan_periode' => fake()->sentence(),
            'apakah_berbayar' => fake()->boolean(),
            'status_periode' => fake()->randomElement([
                PeriodePendaftaran::STATUS_DRAFT,
                PeriodePendaftaran::STATUS_PUBLISHED,
            ]),
            'waktu_dibuka' => $openedAt,
            'waktu_ditutup' => $closedAt,
            'tahun_lulus_akhir' => $lastGraduationYear,
            'tanggal_minimal_batas_lahir' => $startBirthDate,
            'tanggal_maksimal_batas_lahir' => $lastBirthDate,
            'tanggal_awal_daftar_ulang' => $openedAt,
            'tanggal_akhir_daftar_ulang' => $closedAt,
            'waktu_pengumuman_kelulusan' => $closedAt,
            'waktu_pengumuman_nilai' => $closedAt,
            'apakah_tampilkan_daya_tampung' => fake()->boolean(),
            'apakah_tampilkan_nilai' => fake()->boolean(),
            'dapat_mengubah_prodi' => fake()->boolean(),
            'dapat_pilih_prodi_sama' => fake()->boolean(),
            'dapat_pilih_fakultas_sama' => fake()->boolean(),
            'keterangan_finalisasi' => fake()->sentence(),
            'waktu_akhir_finalisasi' => $closedAt,
            'penilaian_rapor' => fake()->randomElement([
                PeriodePendaftaran::REPORT_EVALUATION_YES,
                PeriodePendaftaran::REPORT_EVALUATION_OPTIONAL,
                PeriodePendaftaran::REPORT_EVALUATION_NO,
            ]),
            'batas_tanggal_va' => fake()->numberBetween(1, 14),
            'proses_kelulusan' => fake()->randomElement([
                PeriodePendaftaran::QUALIFICATION_PROCESS_MANUAL,
                PeriodePendaftaran::QUALIFICATION_PROCESS_AUTO_RECOMMENDED,
                PeriodePendaftaran::QUALIFICATION_PROCESS_AUTO_QUALIFIED,
            ]),
        ];
    }
}
