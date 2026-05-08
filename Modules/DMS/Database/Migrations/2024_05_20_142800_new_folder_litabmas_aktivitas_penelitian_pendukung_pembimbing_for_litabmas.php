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
        $litabmas = Folder::firstOrCreate([
            'nama_folder' => 'Litabmas',
            'kode_folder' => Modul::CODE_LITABMAS,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $litabmas->id,
            'nama_folder' => 'Penilaian Administrasi - Pembimbing SK',
            'kode_folder' => FolderStructure::LITABMAS_PENILAIAN_ADMINISTRASI_PEMBIMBING_SK,
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
        $litabmas = Folder::where('kode_folder', Modul::CODE_LITABMAS)->first();

        Folder::where('id_parent', $litabmas->id)
            ->where('kode_folder', FolderStructure::LITABMAS_PENILAIAN_ADMINISTRASI_PEMBIMBING_SK)
            ->delete();
    }
};
