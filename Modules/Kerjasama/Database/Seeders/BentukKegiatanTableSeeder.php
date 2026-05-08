<?php

namespace Modules\Kerjasama\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\JenisKegiatan;
use Modules\Kerjasama\Services\BentukKegiatanManagementService;

class BentukKegiatanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $dataBentukKegiatan = [
            [
                "jenis_kegiatan" => "Penelitian",
                "nama_bentuk_kegiatan" => "Kerjasama Penelitian",
                "keterangan" => "Kolaborasi antara dua atau lebih institusi untuk melakukan penelitian bersama."
            ],
            [
                "jenis_kegiatan" => "Penelitian",
                "nama_bentuk_kegiatan" => "Proyek Penelitian Bersama",
                "keterangan" => "Proyek penelitian yang dilakukan secara kolaboratif oleh beberapa institusi."
            ],
            [
                "jenis_kegiatan" => "Pengabdian",
                "nama_bentuk_kegiatan" => "Program Pengabdian kepada Masyarakat",
                "keterangan" => "Kegiatan yang melibatkan mahasiswa dan dosen dalam memberikan layanan kepada masyarakat."
            ],
            [
                "jenis_kegiatan" => "Pengabdian",
                "nama_bentuk_kegiatan" => "Kegiatan Sosialisasi dan Edukasi",
                "keterangan" => "Kegiatan untuk meningkatkan kesadaran dan pengetahuan masyarakat tentang isu tertentu."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Program Pertukaran Mahasiswa",
                "keterangan" => "Mahasiswa dari perguruan tinggi yang berbeda saling bertukar tempat belajar untuk memperluas wawasan."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Pelatihan dan Workshop",
                "keterangan" => "Kegiatan pelatihan untuk meningkatkan keterampilan dan pengetahuan mahasiswa dan dosen."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "MoU (Memorandum of Understanding)",
                "keterangan" => "Dokumen resmi yang menyatakan kesepakatan kerjasama antara dua institusi."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Konferensi Internasional",
                "keterangan" => "Pertemuan ilmiah yang melibatkan peserta dari berbagai negara untuk membahas topik tertentu."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Seminar Nasional",
                "keterangan" => "Kegiatan diskusi ilmiah yang dihadiri oleh akademisi dan praktisi dari dalam negeri."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Program Magang Bersama",
                "keterangan" => "Kesempatan bagi mahasiswa untuk mendapatkan pengalaman kerja di institusi lain."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Penyelenggaraan Kuliah Umum",
                "keterangan" => "Kegiatan yang menghadirkan pembicara dari luar untuk memberikan kuliah kepada mahasiswa."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Pengembangan Kurikulum Bersama",
                "keterangan" => "Kerjasama dalam merancang dan mengembangkan kurikulum yang relevan."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Penerbitan Jurnal Ilmiah Bersama",
                "keterangan" => "Kerjasama dalam menerbitkan jurnal ilmiah yang melibatkan penulis dari berbagai institusi."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Program Beasiswa Bersama",
                "keterangan" => "Beasiswa yang ditawarkan oleh beberapa institusi untuk mahasiswa berprestasi."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Kegiatan Olahraga Bersama",
                "keterangan" => "Kegiatan olahraga yang melibatkan mahasiswa dari berbagai perguruan tinggi."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Pertukaran Dosen",
                "keterangan" => "Dosen dari satu institusi mengajar di institusi lain untuk periode tertentu."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Penyelenggaraan Festival Akademik",
                "keterangan" => "Kegiatan yang menampilkan karya ilmiah dan seni dari mahasiswa."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Penyelenggaraan Hackathon",
                "keterangan" => "Kompetisi untuk menciptakan solusi teknologi dalam waktu terbatas."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Program Inovasi Teknologi",
                "keterangan" => "Kerjasama dalam mengembangkan teknologi baru yang bermanfaat."
            ],
            [
                "jenis_kegiatan" => "Pendidikan",
                "nama_bentuk_kegiatan" => "Kegiatan Kunjungan Industri",
                "keterangan" => "Kunjungan mahasiswa dan dosen ke industri untuk memahami praktik kerja."
            ]
        ];
        $dataJenisKegiatan = JenisKegiatan::all();

        foreach ($dataBentukKegiatan as $data) {
            $mappingIdJenisKegiatan = $dataJenisKegiatan
                ->where('nama_jenis_kegiatan', $data['jenis_kegiatan'])
                ->first()
                ->id;

            BentukKegiatan::updateOrCreate([
                "nama_bentuk_kegiatan" => $data['nama_bentuk_kegiatan'],
                "keterangan" => $data['keterangan'],
                "id_jenis_kegiatan" => $mappingIdJenisKegiatan
            ]);
        }
    }
}
