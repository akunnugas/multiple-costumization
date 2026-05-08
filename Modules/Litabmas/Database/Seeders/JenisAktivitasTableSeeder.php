<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;

class JenisAktivitasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Analisis Dokumen', 'Diskusi', 'Forum Group Discussion', 'Observasi', 'Penyebaran Angket', 'Wawancara'
        ];

        foreach ($data as $jenis) {
            \Modules\Litabmas\Models\JenisAktivitas::updateOrCreate([
                'nama_jenis_aktivitas' => $jenis
            ]);
        }
    }
}
