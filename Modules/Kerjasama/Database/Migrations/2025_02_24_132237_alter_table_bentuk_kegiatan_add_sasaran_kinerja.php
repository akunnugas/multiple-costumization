<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\BentukKegiatan;
use Modules\Kerjasama\Models\SasaranKinerja;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('kerjasama.mapping_sasaran_bentuk_kegiatan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(BentukKegiatan::class, 'id_bentuk_kegiatan');
            $table->foreignIdTo(SasaranKinerja::class, 'id_sasaran_kinerja');
            $table->logs();
        });
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('kerjasama.mapping_sasaran_bentuk_kegiatan');
    }
};
