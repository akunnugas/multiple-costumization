<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        $indikatorLK11a1 = IndikatorLaporanKinerja::where('nomor_indikator', '1.1.a.1')
            ->where('id_pengisian_panduan', $pengisianPanduan->id)
            ->first();

        $indikatorLK11a1->deskripsi_sumber_data = "<div>1. Kolom 2 &amp; 3 diambil dari menu kepegawaian &gt; pegawai &gt; Daftar Pegawai &gt; Filter: Pegawai dengan hubungan kerja: Dosen Tetap</div><div>(2) Biodata &gt; Kolom Nama Lengkap, Gelar Depan dan Gelar Belakang,&nbsp;</div><div>(3) Pegawai &gt; Daftar Pegawai → Kualifikasi → Pendidikan Formal → Jenjang</div><div>2. Kolom 4 diambil dari menu Kepegawaian &gt; Pegawai &gt; Daftar Pegawai &gt; Biodata &gt; Dosen &gt; Rumpun Bidang Dosen</div><div>3. Kolom 5 diambil dari menu kepegawaian &gt; pegawai &gt; Daftar Pegawai &gt; Kepegawaian &gt; Jabatan Fungsional - Filter: Status Pengajuan Disetujui: Nama Jabatan&nbsp;</div><div>4. Kolom 6 diambil dari menu kepegawaian &gt; pegawai &gt; Daftar Pegawai &gt; Kompetensi &gt; Sertifikasi &gt; Kolom No. Sertifikasi, Status Pengajuan: Disetujui</div><div>5. Kolom 7 diambil dari menu Kepegawaian &gt; Pegawai &gt; Daftar Pegawai → Penunjang → Penunjang Lain: Nama Kegiatan, Instansi Penyelenggara, Tanggal, Status Pengajuan - Disetujui</div>";
        $indikatorLK11a1->save();

        $indikatorLK11c7 = IndikatorLaporanKinerja::where('nomor_indikator', '1.1.c.7')
            ->where('id_pengisian_panduan', $pengisianPanduan->id)
            ->first();
        $indikatorLK11c7->deskripsi_sumber_data = "<div>1. Kolom 1-5 diambil dari modul akademik &gt; Perkuliahan &gt; Data Yudisium &gt; Daftar Yudisium &gt; Filter Status Mahasiswa: Lulus</div><div>Setelah itu lakukan grouping dan counting berdasarkan&nbsp;</div><div>Kolom 2: TS-2</div><div>Kolom 3: TS-1</div><div>Kolom 4: TS</div><div>2. Persentase=( Jumlah TSx - Jumlah TSx−1 : Jumlah TS-x-1) x 100%</div><div>Jika hasilnya negatif, berarti terjadi penurunan</div><div>Jika hasilnya positif, berarti terjadi peningkatan</div>";
        $indikatorLK11c7->save();

        $indikatorLK124 = IndikatorLaporanKinerja::where('nomor_indikator', '1.2.4')
            ->where('id_pengisian_panduan', $pengisianPanduan->id)
            ->first();
        $indikatorLK124->deskripsi_sumber_data = "<div>Kolom 2-9 diambil dari riwayat pegawai (Pembicara, Visiting Scientist, Datasering) berdasarkan:<div>1) menu Pegawai &gt; Daftar Pegawa &gt; Pelaksanaan Pengabdian &gt; Pembicara</div><div>2) menu Pegawai &gt; Daftar Pegawa &gt; Pelaksanaan Pendidikan &gt; Visiting Scientist</div><div>3) menu Pegawai &gt; Daftar Pegawa &gt; Pelaksanaan Pendidikan &gt; Datasering</div></div>";
        $indikatorLK124->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
