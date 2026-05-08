<?php

namespace Modules\SPMI\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\JenjangPendidikan;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\PengisianPanduan;

class PengisianPanduanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // FIXME : JIKA SUDAH PROD HAPUS INI KARENA HANYA BERLAKU DI DEV
        // update or create degree s1
        $degree = JenjangPendidikan::updateOrCreate([
            'kode_jenjang' => 'S1',
        ], [
            'nama_jenjang' => 'Sarjana',
        ]);
        
        $idDegree = $degree->id;
        PengisianPanduan::whereIn('kode_pengisian_panduan',['IAPS9','LEDPS9'])->update(['id_jenjang_pendidikan' => $idDegree]);
    
        $audit = new JadwalAudit();
        $audit->id_audit_periode = 6;
        $audit->tanggal_awal_pengisian = '2024-01-01';
        $audit->tanggal_akhir_pengisian = '2024-01-31';
        $audit->tanggal_awal_penilaian = '2024-02-01';
        $audit->tanggal_akhir_penilaian = '2024-02-28';
        $audit->apakah_audit_aktif = true;
        $audit->apakah_penilaian_mandiri = true;
        $audit->save();

        // // update or create organization
        // $organization = UnitKerja::updateOrCreate([
        //     'code' => '22201',
        // ], [
        //     'name' => 'Teknik Sipil',
        //     'degree_id' => $idDegree,
        //     'is_active' => true,
        //     'type' => UnitKerja::STUDY_PROGRAM,
        // ]);

        // $auditOrganaization = new JadwalAuditUnit();
        // $auditOrganaization->audit_schedule_id = $audit->id;
        // $auditOrganaization->organization_id = $organization->id;
        // $auditOrganaization->save();
    }
}
