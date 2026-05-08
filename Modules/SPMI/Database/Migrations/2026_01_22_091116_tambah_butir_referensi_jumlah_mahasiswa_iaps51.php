<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorKolom;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pengisianPanduan = PengisianPanduan::where([
            'kode_pengisian_panduan' => 'IAPS5.1',
            'apakah_data_default' => true,
        ])->first();

        $referensiParent = IndikatorLaporanKinerja::create([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => 'XREF',
            'nama_indikator_laporan_kinerja' => 'Tabel Referensi',
            'deskripsi' => null,
            'informasi' => null,
            'jenis_layout' => IndikatorLaporanKinerja::LAYOUT_PORTRAIT,
            'jenis_form' => IndikatorLaporanKinerja::FORM_ROW,
            'sumber_data' => IndikatorLaporanKinerja::DATA_MANUAL_INPUT,
            'deskripsi_sumber_data' => null,
            'apakah_import_excel' => null,
            'dapat_lihat_nama_pada_laporan' => true,
            'apakah_aktif' => true,
            'apakah_parent' => true,
            'info_level' => 0,
            'id_parent' => null,
            'apakah_data_default' => true,
            'apakah_memasukkan_kategori_manual' => false,
            'apakah_subfooter' => false,
        ]);

        IndikatorLaporanKinerja::create([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => 'XREF.1',
            'nama_indikator_laporan_kinerja' => '1. Mahasiswa Aktif Program Studi',
            'deskripsi' => "<div>Tambahkan jumlah mahasiswa aktif program studi dalam 4 tahun terakhir. Data ini akan digunakan sebagai landasan auditor untuk memberikan penilaian.</div><div><br>Tabel Referensi:&nbsp;1. Mahasiswa Aktif Program Studi</div><br><br>",
            'informasi' => null,
            'jenis_layout' => IndikatorLaporanKinerja::LAYOUT_PORTRAIT,
            'jenis_form' => IndikatorLaporanKinerja::FORM_ROW,
            'sumber_data' => IndikatorLaporanKinerja::DATA_AKADEMIK,
            'deskripsi_sumber_data' => null,
            'apakah_import_excel' => null,
            'dapat_lihat_nama_pada_laporan' => true,
            'apakah_aktif' => true,
            'info_level' => 1,
            'id_parent' => $referensiParent->id,
            'apakah_data_default' => true,
            'apakah_memasukkan_kategori_manual' => false,
            'apakah_subfooter' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        IndikatorLaporanKinerja::whereIn('nomor_indikator', ['XREF', 'XREF.1'])->delete();
    }
};
