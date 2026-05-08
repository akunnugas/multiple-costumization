<?php

namespace Modules\PMB\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PMB\Models\Seleksi;
use Modules\PMB\Models\SeleksiRuangan;

class SeleksiJadwalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\PMB\Models\SeleksiJadwal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $baseTimestamp = date('Y-m-d H:i:sO');
        $startedAt = $baseTimestamp;
        $endedAt = date('Y-m-d H:i:sO', strtotime('+1 Months', strtotime($baseTimestamp)));
        return [
            'id_seleksi_ruangan' => SeleksiRuangan::factory()->create()->id,
            'id_seleksi' => Seleksi::factory()->create()->id,
            'waktu_mulai' => $startedAt,
            'waktu_selesai' => $endedAt,
        ];
    }
}
