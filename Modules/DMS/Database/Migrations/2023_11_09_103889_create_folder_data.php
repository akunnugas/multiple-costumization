<?php

use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Models\Folder;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $spmi = Folder::firstOrCreate([
            'nama_folder' => 'SPMI',
            'kode_folder' => Modul::CODE_SPMI,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Penjaminan Mutu',
            'kode_folder' => 'spmi_penjaminan_mutu',
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Instrumen Pengisian',
            'kode_folder' => 'spmi_instrumen_pengisian',
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Panduan Penilaian',
            'kode_folder' => 'spmi_panduan_penilaian',
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Dokumen Pengisian',
            'kode_folder' => 'spmi_dokumen_pengisian',
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Surat Keputusan',
            'kode_folder' => 'spmi_surat_keputusan',
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Surat Tugas',
            'kode_folder' => 'spmi_surat_tugas',
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $spmi->id,
            'nama_folder' => 'Berita Acara',
            'kode_folder' => 'spmi_berita_acara',
            'apakah_hanya_lihat' => true
        ]);

        $pmb = Folder::firstOrCreate([
            'nama_folder' => 'PMB',
            'kode_folder' => Modul::CODE_PMB,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $pmb->id,
            'nama_folder' => 'PMB - Pengumuman',
            'kode_folder' => FolderStructure::PMB_PENGUMUMAN,
            'apakah_hanya_lihat' => true
        ]);

        $litabmas = Folder::firstOrCreate([
            'nama_folder' => 'Litabmas',
            'kode_folder' => Modul::CODE_LITABMAS,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $litabmas->id,
            'nama_folder' => 'Petunjuk Teknis',
            'kode_folder' => FolderStructure::LITABMAS_PETUNJUK_TEKNIS,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'nama_folder' => 'DMS',
            'kode_folder' => Modul::CODE_DMS,
            'apakah_hanya_lihat' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
