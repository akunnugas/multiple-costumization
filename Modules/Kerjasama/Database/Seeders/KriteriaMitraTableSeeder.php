<?php

namespace Modules\Kerjasama\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Kerjasama\Models\KriteriaMitra;

class KriteriaMitraTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $dataKriteriaMitra = [
            [
                "klasifikasi_mitra" => "Perusahaan Multinasional",
                "keterangan" => "Perusahaan yang beroperasi di berbagai negara dan memiliki pengaruh global."
            ],
            [
                "klasifikasi_mitra" => "Perusahaan Nasional Berstandar Tinggi",
                "keterangan" => "Perusahaan yang beroperasi di tingkat nasional dengan standar kualitas yang tinggi."
            ],
            [
                "klasifikasi_mitra" => "Perusahaan Teknologi Global",
                "keterangan" => "Perusahaan yang bergerak di bidang teknologi dengan jangkauan pasar internasional."
            ],
            [
                "klasifikasi_mitra" => "Perusahaan Rintisan (Startup Company) Teknologi",
                "keterangan" => "Perusahaan baru yang berfokus pada inovasi dan pengembangan teknologi."
            ],
            [
                "klasifikasi_mitra" => "Organisasi Nirlaba Kelas Dunia",
                "keterangan" => "Organisasi yang beroperasi tanpa tujuan profit dan memiliki reputasi internasional."
            ],
            [
                "klasifikasi_mitra" => "Institusi/Organisasi Multilateral",
                "keterangan" => "Organisasi yang melibatkan beberapa negara dalam kerjasama internasional."
            ],
            [
                "klasifikasi_mitra" => "Perguruan Tinggi QS200",
                "keterangan" => "Perguruan tinggi yang masuk dalam daftar QS200 berdasarkan bidang ilmu."
            ],
            [
                "klasifikasi_mitra" => "Perguruan Tinggi, Fakultas, atau Program Studi Relevan",
                "keterangan" => "Institusi pendidikan yang memiliki program studi yang relevan dengan kerjasama."
            ],
            [
                "klasifikasi_mitra" => "Instansi Pemerintah, BUMN, dan/atau BUMD",
                "keterangan" => "Instansi yang beroperasi di sektor publik dan memiliki peran dalam pengembangan masyarakat."
            ],
            [
                "klasifikasi_mitra" => "Rumah Sakit",
                "keterangan" => "Institusi kesehatan yang menyediakan layanan medis dan penelitian kesehatan."
            ],
            [
                "klasifikasi_mitra" => "UMKM",
                "keterangan" => "Usaha Mikro, Kecil, dan Menengah yang berkontribusi pada perekonomian lokal."
            ],
            [
                "klasifikasi_mitra" => "Lembaga Riset",
                "keterangan" => "Lembaga yang melakukan penelitian di berbagai bidang, baik pemerintah maupun swasta."
            ],
            [
                "klasifikasi_mitra" => "Lembaga Kebudayaan",
                "keterangan" => "Organisasi yang berfokus pada pelestarian dan pengembangan budaya berskala nasional."
            ]
        ];

        foreach ($dataKriteriaMitra as $data) {
            KriteriaMitra::updateOrCreate($data);
        }
    }
}
