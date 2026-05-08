<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\SyaratJenis;
use Modules\PMB\Models\PeriodePendaftaran;
use Modules\PMB\Models\Syarat;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.syarat_pendaftaran', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PeriodePendaftaran::class);
            $table->foreignIdTo(Syarat::class);
            $table->foreignIdTo(SyaratJenis::class);
            $table->boolean('apakah_wajib')->default(false);
            $table->boolean('apakah_upload')->default(false);
            $table->integer('jumlah_dokumen')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('pmb.syarat_pendaftaran', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_periode_pendaftaran', 'id_syarat', 'id_syarat_jenis'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.syarat_pendaftaran');
    }
};
