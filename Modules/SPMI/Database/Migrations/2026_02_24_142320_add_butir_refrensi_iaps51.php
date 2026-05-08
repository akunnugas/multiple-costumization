<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\TarikDataLK;

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

        $referensiParent = IndikatorLaporanKinerja::where([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => 'XREF',
            'apakah_data_default' => true,
        ])->first();

        IndikatorLaporanKinerja::create([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => 'XREF.3',
            'nama_indikator_laporan_kinerja' => '3. Penelitian Dosen Penghitung Rasio',
            'deskripsi' => "<div>Tambahkan data penelitian dosen penghitung rasio program studi dalam 5 tahun terakhir. Data ini akan digunakan sebagai landasan auditor untuk memberikan penilaian.</div><div>Tabel Referensi:&nbsp;3. Penelitian Dosen Penghitung Rasio</div>",
            'informasi' => null,
            'jenis_layout' => IndikatorLaporanKinerja::LAYOUT_PORTRAIT,
            'jenis_form' => IndikatorLaporanKinerja::FORM_ROW,
            'sumber_data' => IndikatorLaporanKinerja::DATA_SDM,
            'deskripsi_sumber_data' => 'Modul Kepegawaian > Pegawai > Pelaksanaan Penelitian > Penelitian',
            'apakah_import_excel' => null,
            'dapat_lihat_nama_pada_laporan' => true,
            'apakah_aktif' => true,
            'info_level' => 1,
            'id_parent' => $referensiParent->id,
            'apakah_data_default' => true,
            'apakah_memasukkan_kategori_manual' => false,
            'apakah_subfooter' => false,
        ]);

        $indikatorLK = IndikatorLaporanKinerja::where('nomor_indikator', 'XREF.3')->first();
        TarikDataLK::create([
            'id_indikator_laporan_kinerja' => $indikatorLK->id,
            'apakah_kurikulum' => false,
            'sumber' => 'siakad_hr',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indikatorLK = IndikatorLaporanKinerja::where('nomor_indikator', 'XREF.3')->first();
        if ($indikatorLK) {
            TarikDataLK::where('id_indikator_laporan_kinerja', $indikatorLK->id)->delete();
            $indikatorLK->delete();
        }
    }
};
