<?php

namespace Modules\SPMI\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

class IndicatorPermanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $butir = IndikatorLaporanKinerja::create([
            'id_pengisian_panduan' => '1',
            'nomor_indikator' => '1',
            'name' => 'Tata Pamong, Tata Kelola, dan Kerjasama',
            'description' => '',
            'information' => '',
            'jenis_form' => 'FR',
            'is_layout_fixed' => false,
            'apakah_menggunakan_kategori' => false,
            'is_category_manual_input' => false,
            'is_using_ts' => false,
            'apakah_subfooter' => false,
            'layout_type' => 'L',
            'data_source' => null,
            'deskripsi_sumber_data' => null,
            'is_import_excel' => false,
            'is_view_in_report' => false,
            'is_view_name_in_report' => false,
            'is_parent' => true,
            'is_active' => true,
            'is_default_data' => true
        ]);

        IndikatorLaporanKinerja::create([
            'id_pengisian_panduan' => '1',
            'nomor_indikator' => '1a',
            'name' => 'a. Kerjasama',
            'description' => 'Tuliskan kerjasama tridharma di Unit Pengelola Program Studi (UPPS) dalam 3 tahun terakhir dengan mengikuti format Tabel 1 berikut ini.


            Tabel 1 Kerjasama Tridharma',
            'information' => 'Keterangan:
                1) Beri tanda V pada kolom yang sesuai.
                2) Diisi dengan judul kegiatan kerjasama yang sudah terimplementasikan, melibatkan sumber daya dan memberikan manfaat bagi Program Studi yang diakreditasi.
                3) Bukti kerjasama dapat berupa Surat Penugasan, Surat Perjanjian Kerjasama (SPK), bukti-bukti pelaksanaan (laporan, hasil kerjasama, luaran kerjasama), atau bukti lain yang relevan. Dokumen Memorandum of Understanding (MoU), Memorandum of Agreement (MoA), atau dokumen sejenis yang memayungi pelaksanaan kerjasama, tidak dapat dijadikan bukti realisasi kerjasama. ',
            'jenis_form' => 'FR',
            'is_layout_fixed' => true,
            'apakah_menggunakan_kategori' => false,
            'is_category_manual_input' => false,
            'is_using_ts' => false,
            'apakah_subfooter' => false,
            'layout_type' => 'L',
            'data_source' => null,
            'deskripsi_sumber_data' => null,
            'is_import_excel' => false,
            'is_view_in_report' => false,
            'is_view_name_in_report' => false,
            'is_parent' => false,
            'is_active' => true,
            'id_parent' => $butir->id,
            'is_default_data' => true
        ]);
    }
}
