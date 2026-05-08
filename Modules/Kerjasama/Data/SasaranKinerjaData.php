<?php

namespace Modules\Kerjasama\Data;

use Arr;
use DB;
use Modules\Kerjasama\Models\IndikatorSasaran;
use Modules\Kerjasama\Models\SasaranKinerja;

class SasaranKinerjaData
{
    
    /**
     * Migrate data sasaran kinerja default dengan metode updateOrCreate
     *
     * @return void
     */
    public function migrate()
    {
        DB::beginTransaction();
        foreach ($this->getData() as $dataSasaran) {
            $modelSasaran = SasaranKinerja::updateOrCreate([
                ...Arr::only($dataSasaran, ["sasaran","keterangan", "level"]),
                "isian_default" => true
            ]);

            foreach ($dataSasaran['dataIndikator'] as $dataIndikator) {
                IndikatorSasaran::updateOrCreate([
                    ...$dataIndikator,
                    "id_sasaran_kinerja" => $modelSasaran->id,
                    "isian_default" => true
                ]);
            }
        }
        DB::commit();
    }
    
    /**
     * Rollback data dengan cara hapus semua data indikator sasaran dan sasaran kinerja
     * jika isian_default => true
     *
     * @return void
     */
    public function rollback()
    {
        DB::beginTransaction();
        IndikatorSasaran::where('isian_default', true)->delete();
        SasaranKinerja::where('isian_default', true)->delete();
        DB::commit();
    }

    protected function getData(): array
    {
        return [
            [
                "sasaran" => "Meningkatnya program studi yang berkualitas",
                "keterangan" => "",
                "level" => "Prioritas Kementrian",
                "dataIndikator" => [
                    [
                        "indikator" => "IKK 2.5.2.1 Persentase Prodi bekerjasama dengan mitra",
                        "keterangan" => "Sesuai dengan Keputusan Menteri Pendidikan dan Kebudayaan Nomor 3/M/2021 tentang Indikator Kinerja Utama Perguruan Tinggi Negeri dan Lembaga Layanan Pendidikan Tinggi di Kementerian Pendidikan dan Kebudayaan, yang dimaksud program studi yang bekerja sama dengan mitra, memiliki kriteria yang sudah ditentukan",
                        "volume" => "0",
                        "satuan" => "%"
                    ]
                ]
            ],
            [
                "sasaran" => "Meningkatnya kualitas lulusan pendidikan tinggi",
                "keterangan" => "",
                "level" => "Prioritas",
                "dataIndikator" => [
                    [
                        "indikator" => "Kesiapan kerja lulusan",
                        "keterangan" => "Persentase lulusan S1 dan D4/D3/D2 yang berhasil: a. mendapat pekerjaan; b. melanjutkan studi; c. menjadi wiraswasta.",
                        "volume" => "0",
                        "satuan" => ""
                    ],
                    [
                        "indikator" => "Mahasiswa di luar kampus",
                        "keterangan" => "Persentase lulusan S1 dan D4/D3/D2 yang: a. menghabiskan paling sedikit 20 SKS di luar kampus; atau b. meraih prestasi paling rendah tingkat nasional.",
                        "volume" => "0",
                        "satuan" => ""
                    ]
                ]
            ],
            [
                "sasaran" => "Meningkatnya inovasi perguruan tinggi dalam rangka meningkatkan mutu pendidikan",
                "keterangan" => "",
                "level" => "Prioritas",
                "dataIndikator" => [
                    [
                        "indikator" => "Link and match PTS",
                        "keterangan" => "Persentase PTS yang berhasil meningkatkan kinerja dengan meningkatkan jumlah dosen yang berkegiatan tridarma di luar kampus dan jumlah program studi yang bekerjasama dengan mitra",
                        "volume" => "0",
                        "satuan" => ""
                    ]
                ]
            ],
            [
                "sasaran" => "Meningkatnya kualitas dosen pendidikan tinggi",
                "keterangan" => "",
                "level" => "Prioritas",
                "dataIndikator" => [
                    [
                        "indikator" => "Dosen di luar kampus",
                        "keterangan" => "Persentase dosen yang berkegiatan tridarma di kampus lain, di QS100 berdasarkan bidang ilmu (QS100 by subject), bekerja sebagai praktisi di dunia industri, atau membina mahasiswa yang berhasil meraih prestasi paling rendah tingkat nasional dalam 5 tahun terakhir.",
                        "volume" => "0",
                        "satuan" => ""
                    ],
                    [
                        "indikator" => "Kualifikasi dosen",
                        "keterangan" => "Persentase dosen tetap: a. berkualifikasi akademik S3; b. memiliki sertifikat kompetensi/profesi yang diakui oleh industri dan dunia kerja; c. berasal dari kalangan praktisi profesional, dunia industri, atau dunia kerja.",
                        "volume" => "0",
                        "satuan" => ""
                    ],
                    [
                        "indikator" => "Penerapan riset dosen",
                        "keterangan" => "Jumlah keluaran penelitian dan pengabdian kepada masyarakat yang berhasil mendapat rekognisi internasional atau diterapkan oleh masyarakat per jumlah dosen.",
                        "volume" => "0",
                        "satuan" => "Hasil Penelitian"
                    ]
                ]
            ],
            [
                "sasaran" => "Meningkatnya kualitas kurikulum dan pembelajaran",
                "keterangan" => "",
                "level" => "Prioritas",
                "dataIndikator" => [
                    [
                        "indikator" => "Kemitraan program studi",
                        "keterangan" => "Persentase program studi S1 dan D4/D3/D2 yang melaksanakan kerja sama dengan mitra.",
                        "volume" => "0",
                        "satuan" => "Kerjasama"
                    ],
                    [
                        "indikator" => "Pembelajaran dalam kelas",
                        "keterangan" => "Persentase mata kuliah S1 dan D4/D3/D2 yang menggunakan metode pembelajaran pemecahan kasus (case method) atau pembelajaran kelompok berbasis proyek (team-based project) sebagai sebagian bobot evaluasi.",
                        "volume" => "0",
                        "satuan" => ""
                    ],
                    [
                        "indikator" => "Akreditasi Internasional",
                        "keterangan" => "Persentase program studi S1 dan D4/D3/D2 yang memiliki akreditasi atau sertifikat internasional yang diakui pemerintah.",
                        "volume" => "0",
                        "satuan" => ""
                    ]
                ]
            ]
        ];
    }
}