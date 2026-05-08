<?php

namespace Modules\SPMI\Database\factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SPMI\Models\AuditPeriode;

class JadwalAuditFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\SPMI\Models\JadwalAudit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fillingDateStart = Carbon::now()->startOfMonth();
        $fillingDateEnd = Carbon::parse($fillingDateStart)->addDays(6);
        $assessmentDateStart = Carbon::parse($fillingDateEnd)->addDays(6);
        $assessmentDateEnd = Carbon::parse($assessmentDateStart)->addDays(6);
        return [
            'id_audit_periode' => AuditPeriode::factory()->create()->id,
            'tanggal_awal_pengisian' => $fillingDateStart,
            'tanggal_akhir_pengisian' => $fillingDateEnd,
            'tanggal_awal_penilaian' => $assessmentDateStart,
            'tanggal_akhir_penilaian' => $assessmentDateEnd,
            'apakah_audit_aktif' => fake()->boolean(),
            'apakah_penilaian_mandiri' => true,
        ];
    }
}
