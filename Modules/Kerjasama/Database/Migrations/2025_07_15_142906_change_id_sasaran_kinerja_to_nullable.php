<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Kerjasama\Models\IndikatorSasaran;
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
        SevimaSchema::table('kerjasama.kegiatan', function (SevimaBlueprint $table) {
            $table->foreignIdFor(SasaranKinerja::class, 'id_sasaran_kinerja')
                ->nullable()
                ->change();

            $table->foreignIdFor(IndikatorSasaran::class, 'id_indikator_sasaran')
                ->nullable()
                ->change();

            $table->string('nomor_dokumen')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('kerjasama.kegiatan', function (SevimaBlueprint $table) {
            $table->foreignIdFor(SasaranKinerja::class, 'id_sasaran_kinerja')
                ->nullable(false)
                ->change();

            $table->foreignIdFor(IndikatorSasaran::class, 'id_indikator_sasaran')
                ->nullable(false)
                ->change();

            $table->string('nomor_dokumen')->nullable(false)->change();
        });
    }
};
