<?php 

namespace Modules\SPMI\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\UnitKerja;

class ComboList {
    /**
     * Mendapatkan array tingkat sertifikasi
     * @return array
     */
    public static function getTingkatSertifikasi() {
        return [
            'Lokal' => 'Lokal/Wilayah',
            'Nasional' => 'Nasional',
            'Internasional' => 'Internasional'
        ];
    }

    /**
     * Mendapatkan array lingkup sertfikasi
     * @return array
     */
    public static function getLingkupSertifikasi() {
        return [
            'PT/ Fakultas' => 'PT/ Fakultas',
            'Unit' => 'Unit'
        ];
    }

    /**
     * Mendapatkan array prodi dibawahnya
     * @return array
     */
    public static function getUnit() {
        $sql = "SELECT id, nama_unit
                FROM core.unit_kerja
                WHERE jenis_unit = ? AND apakah_aktif = '1'";
            
        $data = DB::select($sql, [
            UnitKerja::STUDY_PROGRAM
        ]);
        $data = array_column($data, 'nama_unit', 'nama_unit');
        
        return (array) $data;
    }

    /**
     * Mendapatkan array prodi yang satu fakultas/upps
     * @return array
     */
    public static function getAllProdi() {
        $idunitGlobal = Config::get('spmi.filling_indicator');
        
        $idunit = UnitKerja::find($idunitGlobal);
        if (!$idunit) {
            return [];
        }

        $sql = "SELECT id, nama_unit
                FROM core.unit_kerja
                WHERE id_parent = ? AND jenis_unit = ? AND apakah_aktif = '1'";
        
        $data = DB::select($sql, [
            $idunit->id_parent,
            UnitKerja::STUDY_PROGRAM
        ]);
        $data = array_column($data, 'nama_unit', 'nama_unit');

        return (array) $data;
    }

    /**
     * Mendapatkan array fakultas dan prodi
     * @return array
     */
    public static function getFakultasProdi() {
        $idunitGlobal = Config::get('spmi.filling_indicator');

        $idunit = UnitKerja::find($idunitGlobal);
        if (!$idunit) {
            return [];
        }

        $sql = "SELECT id, nama_unit, info_level
                FROM core.unit_kerja
                WHERE jenis_unit in ? and info_left >= ? and info_right <= ? and apakah_aktif = '1'";
        
        $data = DB::select($sql, [
            [UnitKerja::FACULTY, UnitKerja::STUDY_PROGRAM],
            $idunit->info_left,
            $idunit->info_right
        ]);

        $data = array_column($data, 'nama_unit', 'nama_unit');

        return (array) $data;
    }

    /**
     * Mendapatkan array unit non prodi
     * @return array
     */
    public static function getNonProdi() {
        return [];
    }

    /**
     * Mendapatkan array jabatan fungsional/akdemik
     * @return array
     */
    public static function getJabatanAkademik() {
        return [
            'Tenaga Pengajar' => 'Tenaga Pengajar',
            'Asisten Ahli' => 'Asisten Ahli',
            'Lektor' => 'Lektor',
            'Lektor Kepala' => 'Lektor Kepala',
            'Guru Besar' => 'Guru Besar'
        ];
    }

    /**
     * Mendapatkan array mapping jabatan fungsional/akademik
     * @return array
     */
    public static function getMappingFugsional() {
        return [
            0 => 'Tenaga Pengajar',
            1 => 'Asisten Ahli',
            2 => 'Lektor',
            3 => 'Lektor Kepala',
            4 => 'Guru Besar'
        ];
    }

    /**
     * Mendapatkan array ada atau tidak ada
     * @return array
     */
    public static function getAdaTidak() {
        return [
            1 => 'Ada',
            0 => 'Tidak Ada'
        ];
    }

    /**
     * Mendapatkan array tersedia atau tidak
     * @return array
     */
    public static function getTersediaTidak() {
        return [
            1 => 'Tersedia',
            0 => 'Tidak Tersedia'
        ];
    }

    /**
     * Mendapatkan array status/peringkat akreditasi
     * @return array
     */
    public static function getStatusAkreditasi() {
        return [];
    }

    /**
     * Mendapatkan nama-nama fungsi
     * @return array
     */
    public static function getNamaFungsi() {
        return 'Fungsi mComboAkreditasi yang tersedia: getLingkupSertifikasi, getTingkatSertifikasi, getUnit, getAllProdi, getFakultasProdi, getNonProdi, getJabatanAkademik, getAdaTidak, getTersediaTidak, getStatusAkreditasi';
    }

    /**
     * Mendapatkan dosen tetap
     * @return array
     */
    public static function getDosenTetap() {
        $sql = "SELECT p.id, e.nip, p.nama, wr.name, e.nidn
                FROM core.biodata p
                JOIN core.pegawai e ON e.id_biodata = p.id 
                JOIN hr.work_relations wr ON wr.id = e.id_hubungan_kerja
                    AND wr.waktu_dihapus IS NULL
                JOIN hr.employee_statuses s ON s.id = e.id_status_pegawai
                    AND s.waktu_dihapus IS NULL
                WHERE (
                    (wr.name ILIKE '%Tetap%' AND wr.name NOT ILIKE '%Tidak Tetap%')
                        OR wr.is_pns = true
                )
                    AND e.nidn IS NOT NULL
                    AND s.is_active = true";

        $data = DB::select($sql);
        $data = array_column($data, 'nama', 'nama');
        
        return (array) $data;
    }

    /**
     * Mendapatkan dosen tidak tetap
     * @return array
     */
    public static function getDosenTidakTetap() {
        $sql = "SELECT p.id, e.nip, p.nama, wr.name, e.nidn
                FROM core.biodata p
                JOIN core.pegawai e ON e.id_biodata = p.id 
                JOIN hr.work_relations wr ON wr.id = e.id_hubungan_kerja
                    AND wr.waktu_dihapus IS NULL
                JOIN hr.employee_statuses s ON s.id = e.id_status_pegawai
                    AND s.waktu_dihapus IS NULL
                WHERE NOT (
                    (wr.name ILIKE '%Tetap%' AND wr.name NOT ILIKE '%Tidak Tetap%')
                        OR wr.is_pns = true
                )
                    AND e.nidn IS NOT NULL
                    AND s.is_active = true";

        $data = DB::select($sql);
        $data = array_column($data, 'nama', 'nama');
        
        return (array) $data;
    }

    /**
     * Mendapatkan array jenjang pendidikan
     * @return array
     */
    public static function getJenjangPendidikan() 
    {
        return [
            'D-4/S-1' => 'D-4/S-1',
            'Profesi' => 'Profesi',
            'Sp-1/S-2' => 'Sp-1/S-2',
            'Sp-2/S-3' => 'Sp-2/S-3'
        ];
    }

    /**
     * Mendapatkan array jenjang pendidikan
     * @return array
     */
    public static function getPendidikan() 
    {
        return [
            'Diploma I' => 'Diploma I',
            'Diploma II' => 'Diploma II',
            'Diploma III' => 'Diploma III',
            'Diploma IV' => 'Diploma IV',
            'Sarjana' => 'Sarjana',
            'Profesi' => 'Profesi',
            'Spesialis 1' => 'Spesialis 1',
            'Magister' => 'Magister',
            'Spesialis 2' => 'Spesialis 2',
            'Doktor' => 'Doktor',
        ];
    }

    /**
     * Mendapatkan array tetap atau tidak tetap
     * @return array
     */
    public static function getTetapTidak() 
    {
        return [
            1 => 'Tetap',
            0 => 'Tidak Tetap'
        ];
    }

    /**
     * Mendapatkan array Akademisi atau Praktisi
     * @return array
     */
    public static function getAkademisiPraktisi() 
    {
        return [
            'Akademisi' => 'Akademisi',
            'Praktisi' => 'Praktisi'
        ];
    }

    /**
     * Mendapatkan array Akademik atau Non Akademik
     * @return array
     */
    public static function getAkademikNonAkademik() 
    {
        return [
            1 => 'Akademik',
            0 => 'Non Akademik'
        ];
    }
}