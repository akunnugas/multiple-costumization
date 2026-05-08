<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanAnggota;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\AspekPenilaianIsianProposalService;
use Modules\Litabmas\Services\PengajuanPendanaanService;

class PengajuanPendanaanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // FIXME: belum selesai masih ada yg error
        // set kode_dikti utk unit yg jenis_unitnya Universitas
        $unit = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        if (empty($unit->kode_dikti)) {
            $unit->kode_dikti = 'test_1234';
            $unit->save();
        }

        // prepare data
        $klasterPendanaan = KlasterPendanaan::where('nama_klaster', 'Seeder Klaster Pendanaan Penelitian')->first();
        $bidangIlmu = $klasterPendanaan->pivotBidangIlmu()->first();
        $temaKegiatan = $bidangIlmu->pivotTemaKegiatan()->first();
        $isianProposal = (new AspekPenilaianIsianProposalService())->getByJenisPendanaanDanPeriodePendanaan(
            JenisPendanaanEnum::CODE_PENELITIAN,
            PeriodePendanaan::periodeAktif()->id
        );
        $dosenOpt = DosenEksternal::optionWithBiodata(true, true);
        $ketuaPenelitian = array_key_first($dosenOpt);
        $anggotaPenelitian1 = array_key_last($dosenOpt);

        $dataSumberPendanaan = [
            'judul_penelitian' => 'Seeder Pengajuan Pendanaan Penelitian 1',
            'kode_jenis_pendanaan' => $klasterPendanaan->kode_jenis_pendanaan,
            'id_periode_pendanaan' => PeriodePendanaan::periodeAktif()->id,
            'id_bidang_ilmu' => $bidangIlmu->id,
            'id_tema' => $temaKegiatan->id_tema_kegiatan,
            'id_klaster_pendanaan' => $klasterPendanaan->id,
            'id_sumber_pendanaan' => $klasterPendanaan->id_sumber_pendanaan,
            'nominal_anggaran_diajukan' => $klasterPendanaan->maksimal_anggaran / 2,
            'nama_pemilik_rekening' => 'Seeder Pemilik Rekening',
            'nomor_rekening' => '1234567890',
            'nama_bank' => 'Seeder Bank',
            'cabang_bank' => 'Seeder Cabang Bank',
            'id_foto_tabungan' => null,
            'apakah_berkontribusi_bidang_ilmu' => true,
            'id_dokumen_proposal' => UploadedFile::fake()->create('dokumen_proposal', 1024, 'application/pdf'),
            'id_dokumen_rab' => UploadedFile::fake()->create('dokumen_rab', 1024, 'application/pdf'),
            'id_biodata_leader' => $ketuaPenelitian,
        ];

        foreach ($isianProposal as $isian) {
            $dataSumberPendanaan['isian_proposal__'. JenisPendanaanEnum::CODE_PENELITIAN .'__' . $isian->id] = 'Seeder Isian ' . fake()->sentence;
        }

        $dataSumberPendanaan['_anggota_penelitian'] = [
            [
                'id_biodata' => $anggotaPenelitian1,
                'memberType' => PengajuanPendanaanAnggota::JENIS_DOSEN_INTERNAL
            ]
        ];

        // Store pengajuan pendanaan
        (new PengajuanPendanaanService())->store($dataSumberPendanaan);
    }
}
