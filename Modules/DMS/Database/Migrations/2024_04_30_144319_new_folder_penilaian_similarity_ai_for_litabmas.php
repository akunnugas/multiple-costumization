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
            'nama_folder' => 'Penilaian Administrasi - Penilaian Similarity',
            'kode_folder' => FolderStructure::LITABMAS_PENILAIAN_ADMINISTRASI_SIMILARITY,
            'apakah_hanya_lihat' => true
        ]);

        Folder::firstOrCreate([
            'id_parent' => $litabmas->id,
            'nama_folder' => 'Penilaian Administrasi - Penilaian AI',
            'kode_folder' => FolderStructure::LITABMAS_PENILAIAN_ADMINISTRASI_AI,
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
        //
    }
};
