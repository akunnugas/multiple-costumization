<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\SumberPendanaanService;

class SumberPendanaanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodeAktif = (new PeriodePendanaan)->periodeAktif();
        $univUnitKerja = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        $fakultasUnivKerja = UnitKerja::where('jenis_unit', UnitKerja::FACULTY)->first();
        if (empty($periodeAktif->id)) {
            return;
        }

        // optional agenda
        $agendaKegiatan = (new AgendaKegiatan())->getListCache();
        $optionalAgendaIds = $agendaKegiatan->where('apakah_wajib', false)->pluck('id')->toArray();

        // insert data sumber pendanaan
        $dataSumberPendanaans = [
            [
                'id_periode_pendanaan' => $periodeAktif->id,
                'nama_sumber_pendanaan' => 'Sevima Univ Internal',
                'id_unit_kerja' => $univUnitKerja?->id,
                'kategori_sumber_pendanaan' => SumberPendanaan::CATEGORY_INTERNAL,
                'total_pendanaan' => 100000000,
                'mata_uang' => SumberPendanaan::CURRENCY_IDR,
                'maksimal_toleransi_similarity' => 50.00,
                'maksimal_toleransi_ai' => 50.00,
                'optional_agenda_ids' => $optionalAgendaIds,
            ],
            [
                'id_periode_pendanaan' => $periodeAktif->id,
                'nama_sumber_pendanaan' => 'Sevima 1',
                'id_unit_kerja' => $fakultasUnivKerja?->id,
                'kategori_sumber_pendanaan' => SumberPendanaan::CATEGORY_EXTERNAL,
                'total_pendanaan' => 50000000,
                'mata_uang' => SumberPendanaan::CURRENCY_IDR,
                'maksimal_toleransi_similarity' => 50.00,
                'maksimal_toleransi_ai' => 50.00,
                'optional_agenda_ids' => $optionalAgendaIds,
            ],
            [
                'id_periode_pendanaan' => $periodeAktif->id,
                'nama_sumber_pendanaan' => 'Sevima 2',
                'id_unit_kerja' => $univUnitKerja?->id,
                'kategori_sumber_pendanaan' => SumberPendanaan::CATEGORY_INTERNAL,
                'total_pendanaan' => 15000,
                'mata_uang' => SumberPendanaan::CURRENCY_USD,
                'maksimal_toleransi_similarity' => 50.00,
                'maksimal_toleransi_ai' => 50.00,
                'optional_agenda_ids' => $optionalAgendaIds,
            ],
        ];

        foreach ($dataSumberPendanaans as $dataSumberPendanaan) {
            (new SumberPendanaanService())->store($dataSumberPendanaan);
        }
    }
}
