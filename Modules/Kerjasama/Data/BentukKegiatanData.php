<?php

namespace Modules\Kerjasama\Data;

use DB;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\JenisKegiatan;
use Str;

class BentukKegiatanData 
{
    /**
     * Migrate data sasaran kinerja default dengan metode updateOrCreate
     *
     * @return void
     */
    public function migrate()
    {
        $mappingJenisBentukKerjasama = JenisKegiatan::all()
            ->pluck("id", "nama_jenis_kegiatan");

        DB::beginTransaction();
        foreach ($this->getData() as $bentukKegiatan) {
            BentukKegiatan::updateOrCreate([
                ...$bentukKegiatan,
                'id_jenis_kegiatan' => $mappingJenisBentukKerjasama[Str::title($bentukKegiatan['id_jenis_kegiatan'])],
                'isian_default' => true
            ]);
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

        DB::commit();
    }

    protected function getData(): array
    {
        return [
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Asistensi Mengajar di Satuan Pendidikan-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Gelar Bersama (Joint Degree)',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Gelar Ganda (Dual Degree)',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Kegiatan Wirausaha-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Magang/ Praktik Kerja-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Membangun Desa/KKN Tematik-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Pelatihan',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Pelatihan Dosen dan Instruktur',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Pemagangan',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Penelitian Bersama',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Penelitian Bersama - Artikel/Jurnal Ilmiah',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Penelitian Bersama - Paten',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Penelitian Bersama - Prototipe',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Penelitian/Riset-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Penerbitan Berkala Ilmiah',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Pengabdian Kepada Masyarakat',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Pengembangan Kurikulum/Program Bersama',
            ],
            [
                'id_jenis_kegiatan' => 'penelitian',
                'nama_bentuk_kegiatan' => 'Pengembangan Pusat Penelitian dan Pengembangan Keilmuan',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Pengembangan Sistem / Produk',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Pengiriman Praktisi sebagai Dosen',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Penyaluran Lulusan',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Penyelenggaraan Seminar/Konferensi Ilmiah',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Pertukaran Dosen',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Pertukaran Mahasiswa',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Pertukaran Pelajar-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Proyek Kemanusiaan-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pengabdian',
                'nama_bentuk_kegiatan' => 'Studi/Proyek Independen-Kampus Merdeka',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Transfer Kredit ',
            ],
            [
                'id_jenis_kegiatan' => 'pendidikan',
                'nama_bentuk_kegiatan' => 'Visiting Professor',
            ],
        ];
    }
}