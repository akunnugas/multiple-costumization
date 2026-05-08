<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\DMS\Models\Folder;
use Modules\Gate\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('dms.folder', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Folder::class, 'id_parent', true);
            $table->foreignIdTo(User::class, 'id_pemilik', true);
            $table->string('kode_folder', 50)->nullable()->comment('Kode Folder');
            $table->string('nama_folder', 100)->comment('Nama Folder');
            $table->boolean('apakah_hanya_lihat')->default(false)->comment('Hanya Lihat?');
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();
            $table->integer('info_level')->nullable();
            $table->logs(true);

            $table->index('nama_folder');
            // buat index tree
            $table->index('info_left');
            $table->index('info_right');
            $table->index('info_level');
        });

        DB::statement('CREATE UNIQUE INDEX ON dms.folder (kode_folder) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('dms.folder');
    }
};
