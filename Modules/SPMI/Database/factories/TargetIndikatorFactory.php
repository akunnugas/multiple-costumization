<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianPanduan;

class TargetIndikatorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\TargetIndikator::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'id_lembaga_akreditasi' => LembagaAkreditasi::factory()->create()->id,
            'id_penilaian_panduan' => PenilaianPanduan::factory()->create()->id,
            'id_unit' => UnitKerja::factory()->create()->id,
            'total_matriks' => fake()->randomDigit,
            'apakah_terfinalisasi' => fake()->boolean,
        ];
    }
}
