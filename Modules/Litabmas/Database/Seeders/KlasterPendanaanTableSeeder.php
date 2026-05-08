<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\BidangIlmu;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Models\TemaKegiatan;
use Modules\Litabmas\Services\KlasterPendanaanService;

class KlasterPendanaanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sumberPendanaan = SumberPendanaan::where('nama_sumber_pendanaan', 'Sevima 1')->first();
        $jenisOutput = JenisOutputPenelitian::first();
        $jenisOutcome = JenisOutcomePenelitian::first();

        $bidangIlmuFirst = BidangIlmu::first();
        $temaKegiatanFirst = TemaKegiatan::first();

        $dataKlasterPendanaan = [
            [
                'kode_jenis_pendanaan' => JenisPendanaanEnum::CODE_PENELITIAN,
                'nama_klaster' => 'Seeder Klaster Pendanaan Penelitian',
                'id_sumber_pendanaan' => $sumberPendanaan->id,
                'maksimal_anggaran' => 50000000,
                'kategori_klaster' => KlasterPendanaan::KATEGORI_KELOMPOK,
                'minimal_anggota' => 1,
                'maksimal_anggota' => 2,
                'mata_uang' => SumberPendanaan::CURRENCY_IDR,
                'apakah_butuh_approve_semua_anggota' => true,
                'jenis_output_penelitian' => [
                    $jenisOutput->id => true
                ],
                'jenis_outcome_penelitian' => [
                    $jenisOutcome->id => true
                ],
                '_bidang_ilmu_dan_tema_kegiatan' => [
                    $bidangIlmuFirst->id => [
                        $temaKegiatanFirst->id
                    ]
                ],
                'agenda_kegiatan' => [
                    1 => [
                        'waktu_mulai' => '2024-06-01',
                        'waktu_selesai' => '2024-06-10',
                    ],
                    2 => [
                        'waktu_mulai' => '2024-06-11',
                        'waktu_selesai' => '2024-06-12',
                    ],
                    3 => [
                        'waktu_mulai' => '2024-06-13',
                    ],
                    4 => [
                        'waktu_mulai' => '2024-06-14',
                        'waktu_selesai' => '2024-06-25',
                    ],
                    5 => [
                        'waktu_mulai' => '2024-07-01',
                    ],
                    6 => [
                        'waktu_mulai' => '2024-07-02',
                        'waktu_selesai' => '2024-07-03',
                    ],
                    7 => [
                        'waktu_mulai' => '2024-07-05',
                    ],
                    8 => [
                        'waktu_mulai' => '2024-07-18',
                        'waktu_selesai' => '2024-07-19',
                    ],
                    9 => [
                        'waktu_mulai' => '2024-07-20',
                        'waktu_selesai' => '2024-07-21',
                    ],
                    10 => [
                        'waktu_mulai' => '2024-08-13',
                        'waktu_selesai' => '2024-08-14',
                    ],
                    11 => [
                        'waktu_mulai' => '2024-08-15',
                        'waktu_selesai' => '2024-08-16',
                    ],
                    12 => [
                        'waktu_mulai' => '2024-08-17',
                        'waktu_selesai' => '2024-08-18',
                    ],
                    13 => [
                        'waktu_selesai' => '2027-01-03',
                    ],
                ]
            ]
        ];

        foreach ($dataKlasterPendanaan as $data) {
            (new KlasterPendanaanService())->store($data);
        }
    }
}
