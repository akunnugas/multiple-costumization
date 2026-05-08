<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianMatriksReferensi;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $map = [
            'B106C' => '<div>Jabatan akademik yang diperhitungkan:<br><strong>GB</strong> = Guru Besar<br><strong>LK</strong> = Lektor Kepala<br><br>Rumus perhitungan:<br>PDJA = GB + LK<br><br> Ketentuan pemenuhan indikator:<br><strong>PDJA ≥ 2 → Memenuhi<br></strong><strong>PDJA &lt; 2 → Tidak Memenuhi</strong></div>',
            'B112A' => '<h2>NL</h2><ul><li>NL: Jumlah Lulusan</li><li>Sumber:&nbsp;LKPS Tabel <strong>1-I.7 Profil Jumlah Lulusan</strong></li><li>Periode:&nbsp;3 tahun terakhir (TS-2, TS-1, TS)</li></ul><h3>PK1MTK – Kelulusan Tepat Masa (1× masa tempuh)</h3><ul><li><b>PK1MTK:</b> Persentase Kelulusan Satu Kali Masa Tempuh Kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel 1-I.8d Profil masa studi lulusan pada Program Diploma Dua/Magister/Magister Terapan</li><li><span><b>Definisi:</b>&nbsp;</span>Lulusan TS yang masuk pada <strong>TS-1</strong> dan lulus tepat masa tempuh kurikulum</li><li><p><b>Rumus PK1MTK:&nbsp;</b>(A/B) x 100%</p></li></ul><h3>PK2MTK – Kelulusan ≤ 2× masa tempuh</h3><ul><li>PK2MTK: Persentase Kelulusan 2 kali masa tempuh kurikulum</li><li><b>Sumber:</b>&nbsp;LKPS Tabel <strong>2-I.6a Profil Masa Studi Lulusan</strong></li><li><b>Definisi:</b>&nbsp;Lulusan TS yang masuk pada <strong>TS-6</strong> dan lulus ≤ 2× masa tempuh</li><li><p><b>Rumus PK2MTK:</b>&nbsp;(C/D) x 100%</p></li></ul><h3>RPKID: Rerate Persentase Publikasi Ilmiah DPR</h3><b>RPKID:</b>&nbsp;Rerate Persentase Publikasi Ilmiah DPR<br><b>Sumber:</b>&nbsp;LKPS Tabel <strong>1-II.2 Luaran Penelitian DPRPS dalam bentuk publikasi artikel<br></strong><b>Rumus RPKID:</b>&nbsp;NA1 + NA2 + NA3 + NA4 / Jumlah Keseluruhan Publikasi x 100%<strong><br></strong><br>NMA = Jumlah lulusan dalam 3 tahun terakhir<br>A = Jumlah Lulusan dengan Lama Masa Studi 1,5 ≦ MS ≦ 2,5 TS-1<br>B = Jumlah Mahasiswa Diterima pada TS-1<br>C = Jumlah Lulusan dengan Lama Masa Studi 2,5 < MS ≦ 4 TS-3<br>D = Jumlah Mahasiswa Diterima pada TS-3<p>&nbsp;</p>',
            'B219B' => 'RS (%) = (NAS / NDPRPS) X 100%<br><ul><li>NAS = Jumlah judul artikel yang disitasi</li><li>NDPRPS = Jumlah dosen tetap yang ditugaskan sebagai pengampu mata kuliah di program studi yang diakreditasi.</li></ul>Akreditasi dianggap memenuhi bila&nbsp;<b>RS  ≥ 10%</b><strong>,&nbsp;</strong>jika&nbsp;<b>RS &lt; 10% </b>maka<strong> tidak memenuhi</strong><br>',
        ];

        $penilaianPanduan = PenilaianPanduan::where(['apakah_data_default' => true, 'kode_penilaian_panduan' => 'IAPS5.1-S2-Akre'])->first();

        foreach ($map as $nomorPenilaian => $deskripsi) {
            PenilaianMatriks::where(['id_penilaian_panduan' => $penilaianPanduan->id, 'nomor_penilaian' => $nomorPenilaian])
                ->update(['deskripsi' => $deskripsi]);
        }

        $matriks6e = PenilaianMatriks::where(['id_penilaian_panduan' => $penilaianPanduan->id, 'nomor_penilaian' => 'B106E'])->first();
        $skor6e = SkorMatriksPredikatPenilaian::where(['id_penilaian_panduan' => $penilaianPanduan->id, 'nilai' => 1])->first();
        $predikat6e = PenilaianMatriksPredikat::where(['id_penilaian_matriks' => $matriks6e->id, 'id_skor_matriks_predikat_penilaian' => $skor6e->id])->first();
        $predikat6e->update([
            'deskripsi' => "Beban Kerja DPR yang dinyatakan dalam EWMP diantara 12 sd 16 sks&nbsp;",
        ]);

        $pengisianPanduan = PengisianPanduan::where(['kode_pengisian_panduan' => 'IAPS5.1', 'apakah_data_default' => true])->first();
        $butirLK = IndikatorLaporanKinerja::where([
            'id_pengisian_panduan' => $pengisianPanduan->id,
            'nomor_indikator' => '1.2.5'
        ])->first();

        $matriks17C = PenilaianMatriks::where('id_penilaian_panduan', $penilaianPanduan->id)
            ->where('nomor_penilaian', 'B217C')->first();

        PenilaianMatriksReferensi::where([
            'id_penilaian_matriks' => $matriks17C->id,
            'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
        ])->delete();

        PenilaianMatriksReferensi::insert([
            'id_penilaian_matriks' => $matriks17C->id,
            'id_butir_referensi' => $butirLK->id,
            'jenis_referensi' => PenilaianMatriks::REFERENCE_PERFORMANCE_REPORT,
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
