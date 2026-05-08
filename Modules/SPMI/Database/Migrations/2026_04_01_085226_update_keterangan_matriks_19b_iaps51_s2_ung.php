<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $map = [
            'B219B' => '<div>RS (%) = NAS / NDPRPS X 100<br><br>NAS = Jumlah judul artikel yang disitasi<br>NDPRPS = Jumlah dosen tetap yang ditugaskan sebagai pengampu mata kuliah di program studi yang diakreditasi.<br><br>Akreditasi <b>memenuhi</b> apabila&nbsp;<b>RS ≥ 100%</b><strong>,&nbsp;</strong>jika&nbsp;<b>RS &lt; 100% </b>maka<strong>&nbsp;</strong>Tidak Memenuhi</div>',
            'B112A' => '<h2>NL</h2><ul><li>NL: Jumlah Lulusan</li><li>Sumber:&nbsp;LKPS Tabel&nbsp;<strong>1-I.7 Profil Jumlah Lulusan</strong></li><li>Periode:&nbsp;3 tahun terakhir (TS-2, TS-1, TS)</li></ul><h3>PK1MTK – Kelulusan Tepat Masa (1× masa tempuh)</h3><ul><li><b>PK1MTK:</b>&nbsp;Persentase Kelulusan Satu Kali Masa Tempuh Kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel 1-I.8d Profil masa studi lulusan pada Program Diploma Dua/Magister/Magister Terapan</li><li><b>Definisi:</b>&nbsp;Lulusan TS yang masuk pada&nbsp;<strong>TS-1</strong>&nbsp;dan lulus tepat masa tempuh kurikulum</li><li><p><b>Rumus PK1MTK:&nbsp;</b>(Jumlah Lulusan dengan Lama Masa Studi 1,5≦MS≦2,5 pada TS-1 / Jumlah Mahasiswa Diterima pada TS-1) x 100%</p></li></ul><h3>PK2MTK – Kelulusan ≤ 2× masa tempuh</h3><ul><li>PK2MTK: Persentase Kelulusan 2 kali masa tempuh kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel&nbsp;<strong>2-I.6a Profil Masa Studi Lulusan</strong></li><li><b>Definisi:</b>&nbsp;Lulusan TS yang masuk pada&nbsp;<strong>TS-6</strong>&nbsp;dan lulus ≤ 2× masa tempuh</li><li><p><b>Rumus PK2MTK:</b>&nbsp;<b>&nbsp;</b>(Jumlah Lulusan dengan Lama Masa Studi 2,5≦MS≦4 pada TS-1 / Jumlah Mahasiswa Diterima pada TS-3) x 100%</p></li></ul><h3>RPKID: Rerate Persentase Publikasi Ilmiah DPR</h3><b>RPKID:</b>&nbsp;Rerate Persentase Publikasi Ilmiah DPR<br><b>Sumber:</b>&nbsp;LKPS Tabel&nbsp;<strong>1-II.2 Luaran Penelitian DPRPS dalam bentuk publikasi artikel<br></strong><b>Rumus RPKID:</b>&nbsp;NA1+NA2+NA3+NA4/Jumlah Keseluruhan Publikasi x 100%<br>A = Jumlah Lulusan dengan Lama Masa Studi (Tahun) 1,5 ≤ MS ≤ 2,5<br>B = Jumlah Mahasiswa Diterima pada TS-1<br>C = Jumlah Lulusan dengan Lama Masa Studi (Tahun) 2,5 ≤ MS ≤ 4<br>D = Jumlah Mahasiswa Diterima pada TS-3<strong><br></strong><br>Semua Indikator harus terpenuhi agar Indikator ini dapat <b>memenuhi</b>, diantaranya:<br><ul><li>NL ≤ 12 orang</li><li>PK1MTK≥ 60%</li><li>PK2MTK≥90%</li><li>RPKID≥30%</li></ul>Jika salah satu tidak terpenuhi maka status <b>Tidak Memenuhi</b><br>'
        ];

        $penilaianPanduan = PenilaianPanduan::where(['apakah_data_default' => true, 'kode_penilaian_panduan' => 'IAPS5.1-S2-Ung'])->first();

        foreach ($map as $nomorPenilaian => $deskripsi) {
            PenilaianMatriks::where(['id_penilaian_panduan' => $penilaianPanduan->id, 'nomor_penilaian' => $nomorPenilaian])
                ->update(['deskripsi' => $deskripsi]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
