<?php

namespace Modules\Litabmas\Database\Seeders;

use Illuminate\Database\Seeder;

class TemaKegiatanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Generasi Milenial', 'Isu Jender dan Keadilan', 'Keragaman dalam Etnis, Budaya, Sosial dan Agama', 'Kesejahteraan Sosial da Masyarakat',
            'Lingkungan dan Pengembangan Teknologi', 'Negara, Agama & Masyarakat', 'Pendidikan Transformatif', 'Pengembangan Ekonomi dan Bisnis',
            'Pengembangan Kedokteran dan Kesehatan', 'Pengembangan Pendidikan'
        ];

        foreach ($data as $tema) {
            \Modules\Litabmas\Models\TemaKegiatan::updateOrCreate([
                'nama_tema' => $tema
            ]);
        }
    }
}
