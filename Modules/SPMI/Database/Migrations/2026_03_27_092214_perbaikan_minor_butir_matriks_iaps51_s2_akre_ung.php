<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $deskripsiBaru = [
            'IAPS5.1-S2-Akre'
                => '<h2>NL</h2><ul><li>NL: Jumlah Lulusan</li><li>Sumber:&nbsp;LKPS Tabel <strong>1-I.7 Profil Jumlah Lulusan</strong></li><li>Periode:&nbsp;3 tahun terakhir (TS-2, TS-1, TS)</li></ul><h3>PK1MTK – Kelulusan Tepat Masa (1× masa tempuh)</h3><ul><li><b>PK1MTK:</b> Persentase Kelulusan Satu Kali Masa Tempuh Kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel 1-I.8d Profil masa studi lulusan pada Program Diploma Dua/Magister/Magister Terapan</li><li><span><b>Definisi:</b>&nbsp;</span>Lulusan TS yang masuk pada <strong>TS-1</strong> dan lulus tepat masa tempuh kurikulum</li><li><p><b>Rumus PK1MTK:&nbsp;</b>(Jumlah Lulusan dengan Lama Masa Studi 3,5≦MS≦6 pada TS-3 / Jumlah Mahasiswa Diterima pada TS-3) x 100%</p></li></ul><h3>PK2MTK – Kelulusan ≤ 2× masa tempuh</h3><ul><li>PK2MTK: Persentase Kelulusan 2 kali masa tempuh kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel <strong>2-I.6a Profil Masa Studi Lulusan</strong></li><li><b>Definisi:</b>&nbsp;Lulusan TS yang masuk pada <strong>TS-6</strong> dan lulus ≤ 2× masa tempuh</li><li><p><b>Rumus PK2MTK:</b>&nbsp;(Jumlah Lulusan dengan Lama Masa Studi 6</p></li></ul><h3>RPKID: Rerate Persentase Publikasi Ilmiah DPR</h3><b>RPKID:</b>&nbsp;Rerate Persentase Publikasi Ilmiah DPR<br><b>Sumber:</b>&nbsp;LKPS Tabel <strong>1-II.2 Luaran Penelitian DPRPS dalam bentuk publikasi artikel<br></strong><b>Rumus RPKID:</b>&nbsp;NA1 + NA2 + NA3 + NA4 / Jumlah Keseluruhan Publikasi x 100%<strong><br></strong><br>NMA = Jumlah lulusan dalam 3 tahun terakhir<br>A = Jumlah Lulusan dengan Lama Masa Studi 1,5 ≦ MS ≦ 2,5 TS-1<br>B = Jumlah Mahasiswa Diterima pada TS-1<br>C = Jumlah Lulusan dengan Lama Masa Studi 2,5 < MS ≦ 4 TS-3<br>D = Jumlah Mahasiswa Diterima pada TS-3<p>&nbsp;</p>',
            'IAPS5.1-S2-Ung'
                => '<h2>NL</h2><ul><li>NL: Jumlah Lulusan</li><li>Sumber:&nbsp;LKPS Tabel&nbsp;<strong>1-I.7 Profil Jumlah Lulusan</strong></li><li>Periode:&nbsp;3 tahun terakhir (TS-2, TS-1, TS)</li></ul><h3>PK1MTK – Kelulusan Tepat Masa (1× masa tempuh)</h3><ul><li><b>PK1MTK:</b>&nbsp;Persentase Kelulusan Satu Kali Masa Tempuh Kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel 1-I.8d Profil masa studi lulusan pada Program Diploma Dua/Magister/Magister Terapan</li><li><b>Definisi:</b>&nbsp;Lulusan TS yang masuk pada&nbsp;<strong>TS-1</strong>&nbsp;dan lulus tepat masa tempuh kurikulum</li><li><p><b>Rumus PK1MTK:&nbsp;</b>(Jumlah Lulusan dengan Lama Masa Studi 1,5≦MS≦2,5 pada TS-1 / Jumlah Mahasiswa Diterima pada TS-1) x 100%</p></li></ul><h3>PK2MTK – Kelulusan ≤ 2× masa tempuh</h3><ul><li>PK2MTK: Persentase Kelulusan 2 kali masa tempuh kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel&nbsp;<strong>2-I.6a Profil Masa Studi Lulusan</strong></li><li><b>Definisi:</b>&nbsp;Lulusan TS yang masuk pada&nbsp;<strong>TS-6</strong>&nbsp;dan lulus ≤ 2× masa tempuh</li><li><p><b>Rumus PK2MTK:</b>&nbsp;<b>&nbsp;</b>(Jumlah Lulusan dengan Lama Masa Studi 2,5≦MS≦4 pada TS-1 / Jumlah Mahasiswa Diterima pada TS-3) x 100%</p></li></ul><h3>RPKID: Rerate Persentase Publikasi Ilmiah DPR</h3><b>RPKID:</b>&nbsp;Rerate Persentase Publikasi Ilmiah DPR<br><b>Sumber:</b>&nbsp;LKPS Tabel&nbsp;<strong>1-II.2 Luaran Penelitian DPRPS dalam bentuk publikasi artikel<br></strong><b>Rumus RPKID:</b>&nbsp;A+B+C+D/baris jumlah<br>A = Jumlah Lulusan dengan Lama Masa Studi (Tahun) 1,5 ≤ MS ≤ 2,5<br>B = Jumlah Mahasiswa Diterima pada TS-1<br>C = Jumlah Lulusan dengan Lama Masa Studi (Tahun) 2,5 ≤ MS ≤ 4<br>D = Jumlah Mahasiswa Diterima pada TS-3<strong><br></strong><br>Semua Indikator harus terpenuhi agar Indikator ini dapat <b>memenuhi</b>, diantaranya:<br><ul><li>NL ≤ 12 orang</li><li>PK1MTK≥ 60%</li><li>PK2MTK≥90%</li><li>RPKID≥30%</li></ul>Jika salah satu tidak terpenuhi maka status <b>Tidak Memenuhi</b><br>',
        ];

        $penilaianPanduan = PenilaianPanduan::where('apakah_data_default', true)
            ->whereIn('kode_penilaian_panduan', [
                'IAPS5.1-S2-Akre',
                'IAPS5.1-S2-Ung',
            ])->get();

        foreach ($penilaianPanduan as $p) {
            PenilaianMatriks::where('id_penilaian_panduan', $p->id)
                ->where('nomor_penilaian', 'B112A')
                ->update(['deskripsi' => $deskripsiBaru[$p->kode_penilaian_panduan]]);
        }

        $pengisianPanduan = PengisianPanduan::where(['kode_pengisian_panduan' => 'IAPS5.1', 'apakah_data_default' => true])->first();
        $butirLK = IndikatorLaporanKinerja::where(['id_pengisian_panduan' => $pengisianPanduan->id, 'nomor_indikator' => '1.2.6'])->first();

        $idMatriksAkre = $penilaianPanduan->where('kode_penilaian_panduan', 'IAPS5.1-S2-Akre')->first()->id;
        $matriks15 = PenilaianMatriks::where(['id_penilaian_panduan' => $idMatriksAkre, 'nomor_penilaian' => 'B115'])->first();

        $referensi = [
            'id_penilaian_matriks' => $matriks15->id,
            'id_butir_referensi' => $butirLK->id,
            'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
        ];

        $existingReferensi = PenilaianMatriksReferensi::where([
            'id_penilaian_matriks' => $referensi['id_penilaian_matriks'],
            'id_butir_referensi' => $referensi['id_butir_referensi'],
        ])->first();

        if ($existingReferensi) {
            $existingReferensi->update([
                'jenis_referensi' => $referensi['jenis_referensi'],
            ]);

            return;
        }

        PenilaianMatriksReferensi::query()->insert([$referensi]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
