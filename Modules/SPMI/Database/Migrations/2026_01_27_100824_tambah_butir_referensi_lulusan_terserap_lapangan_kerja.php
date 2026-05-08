<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\SPMI\Models\PengisianPanduan;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\IndikatorLaporanKinerja;

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
            'nomor_indikator' => 'XREF.2',
            'nama_indikator_laporan_kinerja' => '2. Lulusan Terserap Lapangan Kerja/Melanjutkan Jenjang Pendidikan Berikutnya/Berwirausaha',
            'deskripsi' => "<div>Tambahkan data lulusan terserap lapangan kerja/Melanjutkan Jenjang Pendidikan Berikutnya/ Berwirausaha program studi dalam 5 tahun terakhir. Data ini akan digunakan sebagai landasan auditor untuk memberikan penilaian.</div><div>Tabel Referensi:&nbsp;2. Lulusan terserap lapangan kerja/Melanjutkan Jenjang Pendidikan Berikutnya/Berwirausaha</div>",
            'informasi' => null,
            'jenis_layout' => IndikatorLaporanKinerja::LAYOUT_PORTRAIT,
            'jenis_form' => IndikatorLaporanKinerja::FORM_ROW,
            'sumber_data' => IndikatorLaporanKinerja::DATA_AKADEMIK,
            'deskripsi_sumber_data' => 'Karirlink &gt; Dashboard: Kuesioner Lulusan &gt; Waktu Tunggu Lulusan',
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
        //
    }
};
