<?php

namespace Modules\SPMI\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UnitKerja;
use Modules\DMS\Models\Dokumen;
use Modules\SPMI\Models\AuditPeriode;

class DokumenHasilAuditFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\DokumenHasilAudit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_unit' => UnitKerja::factory()->create()->id,
            'id_dokumen' => Dokumen::factory()->create()->id,
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
        ];
    }
}
