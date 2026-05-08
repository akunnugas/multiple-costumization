<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $agendas = [
            'pendaftaran' => 'Pendaftaran',
            'seleksi_administrasi' => 'Seleksi Administrasi',
            'pengumuman_administrasi' => 'Pengumuman Administrasi',
            'feedback_reviewer' => 'Peninjauan Proposal',
            'penentuan_nominasi' => 'Penentuan Nominasi',
            'pengumuman_nominasi' => 'Pengumuman Nominasi',
            'penilaian_hasil_presentasi' => 'Penilaian Hasil Presentasi',
            'penentuan_pendanaan' => 'Penentuan Pendanaan',
            'pengumuman_pendanaan' => 'Pengumuman Lolos Pendanaan',
            'peninjauan_logbook' => 'Pelaksanaan Penelitian/Pengabdian',
            'penilaian_laporan_antara' => 'Pengumpulan Laporan Antara',
            'penilaian_luaran' => 'Pengumpulan Luaran',
            'pengumpulan_hasil' => 'Batas Publikasi',
        ];

        $isRequired = [
            'pendaftaran', 'seleksi_administrasi', 'penentuan_pendanaan', 'penilaian_luaran', 'pengumpulan_hasil'
        ];

        $no = 1;
        foreach ($agendas as $code => $name) {
            $required = in_array($code, $isRequired);

            \Modules\Litabmas\Models\AgendaKegiatan::create([
                'kode_agenda' => $code,
                'urutan' => $no++,
                'nama_agenda' => $name,
                'apakah_wajib' => $required,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Modules\Litabmas\Models\AgendaKegiatan::truncate();
    }
};
