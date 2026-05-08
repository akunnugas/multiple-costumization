<?php

namespace Modules\Admission\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Models\SistemKuliah;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Models\Wilayah;
use Modules\Core\Models\JenisPendaftaran;
use Modules\Core\Models\Sekolah;
use Modules\PMB\Models\Syarat;
use Modules\PMB\Models\Seleksi;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\SebaranAsalPendaftar;
use Modules\PMB\Models\JalurPendaftaran;
use Modules\PMB\Models\PeriodePendaftaran;
use Modules\PMB\Models\SyaratPendaftaran;
use Modules\PMB\Models\SyaratJenis;

class AdmissionDatabaseSeeder extends Seeder
{
    private $institutionTypeSMK;
    private $lectureSystem;
    private $registrationPath;
    private $registrationType;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        // FIXME: Sementara di comment dulu
        // DB::beginTransaction();

        // // [Start] Reference
        // $this->reference();
        // // [End] Reference

        // // [Start] Free Registration Period
        // // periode pendaftaran
        // $registrationPeriod = PeriodePendaftaran::factory()->create([
        //     'kode_periode' => 'REG-2021-1',
        //     'nama_periode' => 'Pendaftaran By Seeder 1',
        //     'id_sistem_kuliah' => $this->lectureSystem->id,
        //     'id_jalur_pendaftaran' => $this->registrationPath->id,
        //     'id_jenis_pendaftaran' => $this->registrationType->id,
        //     'status_periode' => PeriodePendaftaran::STATUS_PUBLISHED,
        //     'waktu_dibuka' => now()->format('Y-m-d\TH:i'),
        //     'waktu_ditutup' => now()->addDays(7)->format('Y-m-d\TH:i'),
        //     'apakah_berbayar' => false,
        //     'apakah_tampilkan_daya_tampung' => true,
        // ]);
        // // untuk sebaran prodi
        // $degree = JenjangPendidikan::firstOrCreate([
        //     'kode_jenjang' => 'S1',
        // ]);
        
        // for ($i = 1; $i <= 3; $i++) {
        //     // prodi
        //     $organization = UnitKerja::factory()->create([
        //         'nama_unit' => 'Prodi Gratis ' . $i,
        //         'id_jenjang_pendidikan' => $degree->id,
        //         'apakah_aktif' => true,
        //         'apakah_aktif_pmb' => true,
        //     ]);

        //     // program distribution/sebaran prodi
        //     $programDistribution = SebaranProdi::factory()->create([
        //         'id_periode_pendaftaran' => $registrationPeriod->id,
        //         'id_unit_kerja' => $organization->id,
        //     ]);

        //     // sebaran prodi pilihan
        //     SebaranAsalPendaftar::factory()->create([
        //         'id_sebaran_prodi' => $programDistribution->id,
        //         'id_jenis_institusi' => $this->institutionTypeSMK->id,
        //     ]);
        // }

        // // get first program distribution
        // $programDistribution = SebaranProdi::where('registration_period_id', $registrationPeriod->id)->first();

        // // seleksi pendaftaran
        // Seleksi::factory()->create([
        //     'id_sebaran_prodi' => $programDistribution->id,
        //     'urutan_seleksi' => 1,
        // ]);

        // // syarat pendafatran (administrasi)
        // SyaratPendaftaran::factory()->create([
        //     'id_periode_pendaftaran' => $registrationPeriod->id,
        //     'id_syarat' => Syarat::factory()->create()->id,
        //     'id_syarat_jenis' => SyaratJenis::where('kode_jenis_syarat', SyaratJenis::CODE_ADMINISTRASI)->first()->id,
        //     'apakah_wajib' => true,
        //     'apakah_upload' => false,
        //     'jumlah_dokumen' => null,
        // ]);
        // // [End] Free Registration Period

        // DB::commit();

        // // clear all cache
        // Cache::clear();
    }

    private function reference()
    {
        $country = Wilayah::factory()->create([
            'nama_wilayah' => 'AA Indonesia',
            'level_wilayah' => Wilayah::LEVEL_COUNTRY,
        ]);
        $province = Wilayah::factory()->create([
            'id_parent' => $country->id,
            'nama_wilayah' => 'AA Jawa Barat',
            'level_wilayah' => Wilayah::LEVEL_PROVINCE,
        ]);
        $city = Wilayah::factory()->create([
            'id_parent' => $province->id,
            'nama_wilayah' => 'AA Kota Bekasi',
            'level_wilayah' => Wilayah::LEVEL_CITY,
        ]);
        $degreeSMK = JenjangPendidikan::factory()->create([
            'nama_jenjang' => 'SMA/SMK Sederajat',
            'apakah_pt' => false,
        ]);
        $this->institutionTypeSMK = JenisInstitusi::factory()->create([
            'nama_jenis_institusi' => 'SMK',
            'id_jenjang_pendidikan' => $degreeSMK->id
        ]);
        JenisInstitusi::factory()->create([
            'nama_jenis_institusi' => 'SMA',
            'id_jenjang_pendidikan' => $degreeSMK->id
        ]);
        Sekolah::factory()->create([
            'nama_sekolah' => 'SMKN 2 Kota Bekasi',
            'id_kota' => $city->id,
            'id_jenis_institusi' => $this->institutionTypeSMK->id,
        ]);
        $this->registrationType = JenisPendaftaran::where('kode_jenis_pendaftaran', JenisPendaftaran::CODE_PDB)->first();

        // jalur pendaftaran
        $this->registrationPath = JalurPendaftaran::factory()->create([
            'nama_jalur' => 'Umum (Pagi)',
        ]);

        // sistem kuliah
        $this->lectureSystem = SistemKuliah::where('nama_sistem', 'Reguler')->first();
    }
}
