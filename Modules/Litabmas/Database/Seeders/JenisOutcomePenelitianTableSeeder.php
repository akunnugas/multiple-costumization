<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Modules\Litabmas\Models\JenisPublikasi;

class JenisOutcomePenelitianTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_outcome' => 'Jurnal Sinta 1',
                'kategori_outcome' => JenisOutcomePenelitian::CATEGORY_PUBLIKASI,
                'batas_pengumpulan_outcome' => 3,
            ],
            [
                'nama_outcome' => 'Jurnal Sinta 2',
                'kategori_outcome' => JenisOutcomePenelitian::CATEGORY_PUBLIKASI,
                'batas_pengumpulan_outcome' => 3,
            ],
            [
                'nama_outcome' => 'Jurnal Sinta 3',
                'kategori_outcome' => JenisOutcomePenelitian::CATEGORY_PUBLIKASI,
                'batas_pengumpulan_outcome' => 2,
            ],
            [
                'nama_outcome' => 'Jurnal Internasional bereputasi terindeks',
                'kategori_outcome' => JenisOutcomePenelitian::CATEGORY_PATEN_HKI,
                'batas_pengumpulan_outcome' => 3,
            ],
            [
                'nama_outcome' => 'Buku ber-ISBN',
                'kategori_outcome' => JenisOutcomePenelitian::CATEGORY_PUBLIKASI_PATEN_HKI,
                'batas_pengumpulan_outcome' => 2,
            ]
        ];

        foreach ($data as $key => $item) {
            $idJenisPublikasi = JenisPublikasi::where('id', ($key + 1))->first()->id;

            JenisOutcomePenelitian::updateOrCreate([
                'id_jenis_publikasi' => $idJenisPublikasi,
                'nama_outcome' => $item['nama_outcome'],
                'kategori_outcome' => $item['kategori_outcome'],
                'batas_pengumpulan_outcome' => $item['batas_pengumpulan_outcome'],
            ]);
        }
    }
}
