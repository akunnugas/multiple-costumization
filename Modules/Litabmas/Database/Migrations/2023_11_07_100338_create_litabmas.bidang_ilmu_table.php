<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.bidang_ilmu', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('ref_key_siakad', 255)->comment('Kolom PK SIAKAD V1');
            $table->string('nama_bidang_ilmu')->comment('Nama Bidang Ilmu');
            $table->string('info_parent')->nullable()->comment('Parent Bidang Ilmu');
            $table->unsignedTinyInteger('info_level')->nullable()->default(1)->comment('Level Bidang Ilmu');
            $table->unsignedSmallInteger('info_left')->nullable()->comment('Info Left');
            $table->unsignedSmallInteger('info_right')->nullable()->comment('Info Right');

            $table->logs();
        });

        // unique index
        DB::statement('CREATE UNIQUE INDEX ON litabmas.bidang_ilmu (ref_key_siakad) where waktu_dihapus is null');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.bidang_ilmu');
    }
};
