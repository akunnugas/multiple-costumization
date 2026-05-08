<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\PeriodePendanaan;

class AspekPenilaianKomposisiProposalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\AspekPenilaianKomposisiProposal::class;

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
            'nama_komposisi_proposal' => fake()->unique()->name(),
            'bobot_komposisi_proposal' => fake()->randomFloat(2, 1, 100),
        ];
    }
}
