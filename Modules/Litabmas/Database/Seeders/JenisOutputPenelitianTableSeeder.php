<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Litabmas\Models\JenisOutputPenelitian;

class JenisOutputPenelitianTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Laporan Penelitian', 'Draft Buku Ajar', 'Draft Buku', 'Draft Artikel', 'Dokumen Feasibility',
            'Dokumen Business Plan', 'Policy Brief, Rekomendasi Kebijakan, Model Kebijakan Strategis'
        ];

        foreach ($data as $namaOutput) {
            JenisOutputPenelitian::updateOrCreate([
                'nama_output' => $namaOutput
            ]);
        }
    }
}
