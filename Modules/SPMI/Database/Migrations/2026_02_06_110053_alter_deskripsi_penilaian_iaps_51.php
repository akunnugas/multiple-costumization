<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $matriksData = [
            'B106B' => 'DPR = Dosen Penghitung Rasio',

            'B106C' => 'PDJA=(GB+LK+L+AA)/ NDPR
GB = Jumlah dosen penghitung rasio yang memiliki jabatan akademik Guru Besar
LK = Jumlah dosen penghitung rasio yang memiliki jabatan akademik Lektor Kepala
L = Jumlah dosen penghitung rasio yang memiliki jabatan akademik Lektor
AA = Jumlah dosen penghitung rasio yang memiliki jabatan akademik Asisten Ahli
NDPR = Jumlah Dosen  Penghitung Rasio',

            'B106D' => 'NDTT = Jumlah Dosen Tidak Tetap
NDPR = Jumlah Dosen Penghitung Rasio
PDTT = Persentase Dosen Tetap
PDTT = (NDTT / (NDTT + NDPR)) x 100%',

            'B106E' => 'EWMP = Ekuivalensi Waktu Mengajar Penuh',

            'B108C' => 'A = Rata-rata Biaya Operasional Pendidikan (3 tahun)
B = Rata-rata Biaya Investasi (3 tahun)
Total Anggaran Pendidikan = A + B
Proporsi Investasi = (B / (A+B)) x 100%
Rata-rata jumlah mahasiswa aktif (3 tahun) = (data TS + data TS-1 + data TS-2) / 3
Biaya Operasional per Mahasiswa = A / Rata-rata Jumlah Mahasiswa Aktif (3 Tahun)',

            'B110C' => 'NM_10sks = jumlah mahasiswa aktif pada TS dengan total SKS “di luar Prodi” ≥ 10.
NM = jumlah mahasiswa aktif pada TS.
Persentase = (Jumlah mahasiswa beban belajar ≥ 10 SKS / Jumlah mahasiswa saat TS) x 100%',

            'B112A' => 'Re-PL = Rerata persentase penurunan lulusan (Sarjana ) dalam 3 tahun terakhir
PK1MTK = Persentase Kelulusan 1x Masa Tempuh Kurikulum
PK2MTK = Persentase Kelulusan ≤ 2x Masa Tempuh Kurikulum
RPMP = Persentase keterlibatan mahasiswa aktif dalam memperoleh prestasi mahasiswa
A = Jumlah Lulusan dengan Lama Masa Studi 3,5≦MS≦6 pada TS-3
B = Jumlah Mahasiswa Diterima pada TS-3
C = Jumlah Lulusan dengan Lama Masa Studi 6<MS≦8,0 pada TS-6
D = Jumlah Mahasiswa Diterima pada TS-6
PK1MTK = (A / B) x 100%
PK2MTK = (C / D) x 100%
RPMP = (Jumlah mahasiswa aktif berprestasi/ Jumlah mahasiswa aktif TS) x 100%',

            'B115' => 'PLTLK = Persentase lulusan terserap lapangan kerja
RPPM = Rerata persentase penurunan mahasiswa baru dalam 3 tahun terakhir
N_absorb_1y = Jumlah mahasiswa lulus terserap kerja <= 12
N_traced = Jumlah mahasiswa lulus pada TS-2
PLTLK = (N_absorb_1y / N_traced) x 100%',

            'B218C' => 'RLP (%) = (NA1 + NA2 + NA3 + NA4 + NB1 + NB2 + NB3) / NDPR X 100
NA1 = Jumlah publikasi di jurnal nasional tidak terakreditasi.
NA2 = Jumlah publikasi di jurnal nasional terakreditasi.
NA3 = Jumlah publikasi di jurnal internasional.
NA4 = Jumlah publikasi di jurnal internasional bereputasi.
NB1 = Jumlah publikasi di seminar wilayah/lokal/perguruan tinggi.
NB2 = Jumlah publikasi di seminar nasional.
NB3 = Jumlah publikasi di seminar internasional.
NC1 = Jumlah pagelaran/pameran/presentasi dalam forum di tingkat wilayah.
NC2 = Jumlah pagelaran/pameran/presentasi dalam forum di tingkat nasional.
NC3 = Jumlah pagelaran/pameran/presentasi dalam forum di tingkat internasional.
NDPR = Jumlah dosen tetap yang ditugaskan di Program studi yang diakreditasi.',

            'B323A' => 'RRD (%) = NRD / NDPR X 100
NRD = Jumlah dosen yang mendapat pengakuan atas prestasi/kinerja dalam 3 tahun terakhir.
NDPR = Jumlah dosen tetap yang ditugaskan di program studi yang diakreditasi.',

            'B323B' => 'RHKI (%) = (NA + NB + NC) / NDPR X 100
NA = Jumlah luaran PkM yang mendapat pengakuan HKI (Paten, Paten Sederhana)
NB = Jumlah luaran PkM yang mendapat pengakuan HKI (Desain Produk Industri, Perlindungan Varietas Tanaman, Desain Tata Letak Sirkuit Terpadu, dll.)
NC = Jumlah luaran PkM dalam bentuk Teknologi Tepat Guna, Produk (Produk Terstandarisasi, Produk Tersertifikasi), Karya Seni, Rekayasa Sosial.
NDPR = Jumlah dosen tetap yang ditugas di Program studi yang diakreditasi.',
        ];

        $penilaianPanduan = PenilaianPanduan::where(['kode_penilaian_panduan' => 'IAPS5.1-S1-Akre', 'apakah_data_default' => true])->first();
        foreach ($matriksData as $kodeMatriks => $deskripsi) {
            $matriks = PenilaianMatriks::where([
                'nomor_penilaian' => $kodeMatriks,
                'id_penilaian_panduan' => $penilaianPanduan->id,
                'apakah_data_default' => true,
            ])->first();
            if ($matriks) {
                $matriks->update(['deskripsi' => $deskripsi]);
            }
        }

        // ganti deskripsi matriks B112A
        $skorMatriksPredikat = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $penilaianPanduan->id)->where('nilai', 1)->first();
        $matriks = PenilaianMatriks::where([
            'nomor_penilaian' => 'B112A',
            'id_penilaian_panduan' => $penilaianPanduan->id,
            'apakah_data_default' => true,
        ])->first();
        $skorPredikat = PenilaianMatriksPredikat::where('id_penilaian_matriks', $matriks->id)
            ->where('id_skor_matriks_predikat_penilaian', $skorMatriksPredikat->id)
            ->first();

        $skorPredikat->deskripsi = $skorPredikat->deskripsi . ' <br><b>Catatan: Tidak diperhitungkan untuk Program Studi Sarjana  yang baru beroperasi kurang dari 2 tahun, skor =1</b>';
        $skorPredikat->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
