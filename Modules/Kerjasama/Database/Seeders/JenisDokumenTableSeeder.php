<?php

namespace Modules\Kerjasama\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Kerjasama\Models\JenisDokumen;

class JenisDokumenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $datajenisDokumen = [
            "Proposal",
            "Laporan",
            "Surat Perjanjian",
            "Notulen Rapat",
            "Dokumen Keuangan",
            "Sertifikat",
            "Brosur",
            "Katalog",
            "Dokumen Kebijakan",
            "Formulir"
        ];

        foreach ($datajenisDokumen as $jenisDokumen) {
            JenisDokumen::updateOrCreate(['jenis_dokumen' => $jenisDokumen]);
        }
    }
}
