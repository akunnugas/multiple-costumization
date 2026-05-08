<?php

use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Models\Folder;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $kerjasama = Folder::firstOrCreate([
            'nama_folder' => 'SPMI',
            'kode_folder' => Modul::CODE_KERJASAMA,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $kerjasama->id,
            'nama_folder' => 'Kerjasama Mitra',
            'kode_folder' => FolderStructure::KERJASAMA_MITRA,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $kerjasama->id,
            'nama_folder' => 'Kerjasama Data',
            'kode_folder' => FolderStructure::KERJASAMA_DATA,
            'apakah_hanya_lihat' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
