<?php

use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Models\Folder;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $litabmas = Folder::firstOrCreate([
            'nama_folder' => 'Litabmas',
            'kode_folder' => Modul::CODE_LITABMAS,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $litabmas->id,
            'nama_folder' => 'Pengajuan Pendanaan - Proposal',
            'kode_folder' => FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_PROPOSAL,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $litabmas->id,
            'nama_folder' => 'Pengajuan Pendanaan - RAB',
            'kode_folder' => FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_RAB,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $litabmas->id,
            'nama_folder' => 'Pengajuan Pendanaan - Buku Tabungan',
            'kode_folder' => FolderStructure::LITABMAS_PENGAJUAN_PENDANAAN_BUKU_TABUNGAN,
            'apakah_hanya_lihat' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
