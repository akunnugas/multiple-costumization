<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\PegawaiManagementService;
use Modules\Litabmas\Services\JenisOutcomePenelitianService;
use Modules\Litabmas\Services\BidangIlmuService;

class LitabmasDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        // jenis publikasi dari siakad
        (new JenisOutcomePenelitianService())->jenisPublikasiFromHRSiakadV1();

        // sync bidang ilmu dari siakad
        (new BidangIlmuService())->syncBidangIlmuFromHRSiakadV1();
        // sync pegawai
        (new PegawaiManagementService())->syncFromSiakadv1();

        // output dan outcome
        $this->call(JenisOutputPenelitianTableSeeder::class);
        $this->call(JenisOutcomePenelitianTableSeeder::class);

        // master umum
        $this->call(TemaKegiatanTableSeeder::class);
        $this->call(JenisAktivitasTableSeeder::class);
        $this->call(PeriodePendanaanTableSeeder::class);
        $this->call(AspekPenilaianIsianProposalTableSeeder::class);

        // sumber pendanaan
        $this->call(SumberPendanaanTableSeeder::class);

        // klaster pendanaan
        $this->call(KlasterPendanaanTableSeeder::class);

        // pengajuan pendanaan
//        $this->call(PengajuanPendanaanTableSeeder::class);
    }
}
