<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\AspekPenilaianIsianProposal;
use Modules\Litabmas\Models\PeriodePendanaan;

class AspekPenilaianIsianProposalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodePendanaan = PeriodePendanaan::where('tanggal_mulai', '<=', now())
            ->where('tanggal_akhir', '>=', now())
            ->select('id')
            ->first();

        $dataPenelitian = [
            'Judul Penelitian', 'Latar Belakang', 'Rumusan Masalah', 'Tujuan Penelitian', 'Kajian Peneliti Terdahulu',
            'Konsep atau Teori Relevan', 'Metode Penelitian','Rencana Pembahasan', 'Daftar Pustaka'
        ];

        $dataPengabdian = [
            'Judul Pengabdian Masyarakat', 'Latar Belakang', 'Fokus Pengabdian Masyarakat', 'Tujuan Pengabdian Masyarakat',
            'Analisis Strategi Pengabdian Masyarakat', 'Kajian Terdahulu yang relevan', 'Konsep / Teori yang relevan',
            'Metodologi Pengabdian Masyarakat', 'Daftar Pustaka'
        ];

        foreach ($dataPenelitian as $key => $value) {
            AspekPenilaianIsianProposal::updateOrCreate(
                [
                    'id_periode_pendanaan' => $periodePendanaan->id,
                    'kode_jenis_pendanaan' => JenisPendanaanEnum::CODE_PENELITIAN,
                    'nama_isian_proposal' => $value,
                ],
                [
                    'urutan_isian_proposal' => $key + 1,
                ]
            );
        }

        foreach ($dataPengabdian as $key => $value) {
            AspekPenilaianIsianProposal::updateOrCreate(
                [
                    'id_periode_pendanaan' => $periodePendanaan->id,
                    'kode_jenis_pendanaan' => JenisPendanaanEnum::CODE_PENGABDIAN,
                    'nama_isian_proposal' => $value,
                ],
                [
                    'urutan_isian_proposal' => $key + 1,
                ]
            );
        }
    }
}
