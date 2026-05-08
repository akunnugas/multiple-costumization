<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\JadwalAudit;

class JadwalAuditUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\JadwalAuditUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_jadwal_audit' => JadwalAudit::factory()->create()->id,
            'id_unit' => UnitKerja::factory()->create()->id,
        ];
    }
}
