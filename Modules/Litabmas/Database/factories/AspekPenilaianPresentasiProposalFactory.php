<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\PeriodePendanaan;

class AspekPenilaianPresentasiProposalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\AspekPenilaianPresentasiProposal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $jenisPendanaanCodes = JenisPendanaanEnum::CODES;

        return [
            'id_periode_pendanaan' => PeriodePendanaan::factory()->create()->id,
            'kode_jenis_pendanaan' => fake()->randomElement($jenisPendanaanCodes),
            'pertanyaan_presentasi_proposal' => fake()->unique()->text(),
            'bobot_pertanyaan_presentasi_proposal' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
