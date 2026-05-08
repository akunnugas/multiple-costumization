<?php

namespace Modules\Litabmas\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\Cache\PeriodePendanaanCache;

class AspekPenilaianIsianProposalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Litabmas\Models\AspekPenilaianIsianProposal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $activePeriod = PeriodePendanaanCache::periodeAktif();

        return [
            'id_periode_pendanaan' => $activePeriod->id,
            'kode_jenis_pendanaan' => JenisPendanaanEnum::CODE_PENELITIAN,
            'urutan_isian_proposal' => $this->faker->unique()->numberBetween(1, 10),
            'nama_isian_proposal' => $this->faker->sentence,
        ];
    }
}

