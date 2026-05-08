<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\BidangIlmu;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Models\TemaKegiatan;

class PengajuanPendanaanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\PengajuanPendanaan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $jenisPendanaanCodes = JenisPendanaanEnum::CODES;

        return [
            'kode_jenis_pendanaan' => fake()->randomElement($jenisPendanaanCodes),
            'id_sumber_pendanaan' => SumberPendanaan::factory()->create()->id,
            'id_klaster_pendanaan' => KlasterPendanaan::factory()->create()->id,
            'id_tema_kegiatan' => TemaKegiatan::factory()->create()->id,
            'id_bidang_ilmu' => BidangIlmu::factory()->create()->id,
            'judul_penelitian' => fake()->text(255),
            'apakah_berkontribusi_bidang_ilmu' => fake()->boolean(),
            'id_agenda_kegiatan' => StatusAgenda::factory()->create()->id,
            'kode_registrasi' => fake()->text(255),
            'id_dokumen_proposal' => ProposalDocument::factory()->create()->id,
            'waktu_snk_disetujui' => fake()->iso8601(),
            'mata_uang' => fake()->text(3),
            'nominal_anggaran_diajukan' => fake()->randomFloat(2, 1, 100),
            'nominal_anggaran_disetujui' => fake()->randomFloat(2, 1, 100),
            'nominal_anggaran_terpakai' => fake()->randomFloat(2, 1, 100),
            'persentase_anggaran_dicairkan' => fake()->randomFloat(2, 1, 100),
            'id_dokumen_rab' => RabDocument::factory()->create()->id,
            'proposal_valid_oleh' => fake()->text(),
            'apakah_lolos_nominasi' => fake()->boolean(),
            'waktu_lolos_nominasi' => fake()->iso8601(),
            'lolos_nominasi_oleh' => fake()->text(),
            'apakah_lolos_pendanaan' => fake()->boolean(),
            'waktu_lolos_pendanaan' => fake()->iso8601(),
            'lolos_pendanaan_oleh' => fake()->text(),
            'penilaian_index_similarity' => fake()->randomFloat(2, 1, 100),
            'id_dokumen_penilaian_similarity' => AssessmentSimilarityDocument::factory()->create()->id,
            'penilaian_index_ai' => fake()->randomFloat(2, 1, 100),
            'id_dokumen_penilaian_ai' => AssessmentAiDocument::factory()->create()->id,
            'total_nilai_komposisi_proposal' => fake()->randomFloat(2, 1, 100),
            'total_nilai_presentasi_proposal' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
