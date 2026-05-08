<?php

namespace Modules\SPMI\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\IndikatorEvaluasiDiri;

class IndikatorEvaluasiDiriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $indicator = IndikatorEvaluasiDiri::create([
            'id_pengisian_panduan' => '2',
            'nomor_indikator' => '1',
            'name' => 'Pendahuluan',
            'description' => 'Evaluasi diri mencakup keseluruhan evaluasi diri UPPS yang bertanggung jawab menyelenggarakan program studi yang diakreditasi (mengacu kepada PP nomor 4 tahun 2014, Struktur Organisasi dan Tata Kerja masing-masing Perguruan Tinggi).



            Bagian ini berisi deskripsi yang memuat dasar penyusunan, tim penyusun, dan mekanisme kerja penyusunan LED.',
            'is_parent' => true,
            'is_comment' => false,
            'is_active' => true,
            'is_default_data' => true,
            'is_key_point' => false
        ]);

        IndikatorEvaluasiDiri::create([
            'id_pengisian_panduan' => '2',
            'nomor_indikator' => '1A',
            'name' => 'Dasar Penyusunan',
            'description' => 'Evaluasi diri mencakup keseluruhan evaluasi diri UPPS yang bertanggung jawab menyelenggarakan program studi yang diakreditasi (mengacu kepada PP nomor 4 tahun 2014, Struktur Organisasi dan Tata Kerja masing-masing Perguruan Tinggi).



            Bagian ini berisi deskripsi yang memuat dasar penyusunan, tim penyusun, dan mekanisme kerja penyusunan LED.',
            'id_parent' => $indicator->id,
            'is_parent' => true,
            'is_comment' => false,
            'is_active' => true,
            'is_default_data' => true,
            'is_key_point' => false
        ]);
    }
}
