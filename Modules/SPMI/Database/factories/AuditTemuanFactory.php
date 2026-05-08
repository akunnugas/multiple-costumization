<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\PenilaianMatriks;

class AuditTemuanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\AuditTemuan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_penilaian_audit' => PenilaianAudit::factory()->create()->id,
            'id_penilaian_matriks' => PenilaianMatriks::factory()->create()->id,
            'uraian_temuan_audit' => fake()->text(),
            'rencana_peningkatan_mutu' => fake()->text(),
            'pelaksana' => fake()->text(255),
            'tanggal_peningkatan_mutu' => fake()->text(),
            'jenis_temuan' => fake()->randomElement([1, 2, 3]),
        ];
    }
}
