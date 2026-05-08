<?php

namespace Modules\Kerjasama\Data;

use DB;
use Modules\Kerjasama\Models\KriteriaMitra;

class KriteriaMitraData
{
    
    /**
     * Migrate data Kriteria mitra default dengan metode updateOrCreate
     *
     * @return void
     */
    public function migrate()
    {
        DB::beginTransaction();
        foreach ($this->getData() as $kriteriaMitra) {
            KriteriaMitra::updateOrCreate([
                ...$kriteriaMitra,
                'isian_default' => true
            ]);
        }
        DB::commit();
    }
    
    /**
     * Rollback data dengan cara hapus semua data kriteria mitra
     * jika isian_default => true
     *
     * @return void
     */
    public function rollback()
    {
        DB::beginTransaction();
        KriteriaMitra::where('isian_default', true)->delete();
        DB::commit();
    }

    protected function getData(): array
    {
        return [
            ['klasifikasi_mitra' => 'Perusahaan multinasional', 'bobot' => 0.75],
            ['klasifikasi_mitra' => 'Perusahaan nasional berstandar tinggi', 'bobot' => null],
            ['klasifikasi_mitra' => 'Perusahaan teknologi global', 'bobot' => 1],
            ['klasifikasi_mitra' => 'Perusahaan rintisan (startup company) teknologi', 'bobot' => 0.5],
            ['klasifikasi_mitra' => 'Organisasi nirlaba kelas dunia', 'bobot' => 0.75],
            ['klasifikasi_mitra' => 'Institusi/ Organisasi multilateral', 'bobot' => 1],
            ['klasifikasi_mitra' => 'Perguruan tinggi dalam negeri dalam daftar QS200 berdasarkan bidang ilmu', 'bobot' => 0.5],
            ['klasifikasi_mitra' => 'Perguruan tinggi luar negeri dalam daftar QS200 berdasarkan bidang ilmu', 'bobot' => 1],
            ['klasifikasi_mitra' => 'Instansi pemerintah Pusat dan/atau Daerah BUMN dan/atau BUMD', 'bobot' => 0.5],
            ['klasifikasi_mitra' => 'Rumah Sakit', 'bobot' => 0.3],
            ['klasifikasi_mitra' => 'Dunia Usaha', 'bobot' => null],
            ['klasifikasi_mitra' => 'Institusi Pendidikan', 'bobot' => null],
            ['klasifikasi_mitra' => 'Organisasi / Instansi pemerintahan', 'bobot' => 0.3],
            ['klasifikasi_mitra' => 'Perguruan tinggi, fakultas, atau program studi dalam bidang yang relevan', 'bobot' => null],
            ['klasifikasi_mitra' => 'Lembaga riset pemerintah, swasta, nasional, maupun internasional', 'bobot' => 0.3],
            ['klasifikasi_mitra' => 'Lembaga kebudayaan berskala nasional/ bereputasi', 'bobot' => 0.3]
        ];
    }
}