<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Pegawai;
use Modules\SPMI\Models\SkAuditor;

class SkAuditorPegawaiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\SkAuditorPegawai::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_sk_auditor' => SkAuditor::factory()->create()->id,
            'id_personil' => Pegawai::factory()->create()->person_id,
        ];
    }
}
