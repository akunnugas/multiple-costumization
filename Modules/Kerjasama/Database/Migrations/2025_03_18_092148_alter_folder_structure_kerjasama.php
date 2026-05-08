<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\DMS\Helpers\FolderStructure;
use Modules\DMS\Models\Folder;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = Folder::where('kode_folder', FolderStructure::KERJASAMA);
        $kerjasama = $query->firstOrFail();
        $query->update([
            'nama_folder' => 'Kerjasama'
        ]);
        
        Folder::firstOrCreate([
            'id_parent' => $kerjasama->id,
            'nama_folder' => 'Kerjasama Kegiatan',
            'kode_folder' => FolderStructure::KERJASAMA_KEGIATAN,
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
