<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\PMB\Models\PeriodePendaftaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.sebaran_prodi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PeriodePendaftaran::class);
            $table->foreignIdTo(UnitKerja::class, nullable: true);
            $table->integer('daya_tampung')->nullable();
            $table->float('nilai_minimal')->nullable();
            $table->string('prefix_nim')->nullable();
            $table->integer('digit_nim_maksimal')->nullable();
            $table->logs();
        });

        SevimaSchema::table('pmb.sebaran_prodi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_periode_pendaftaran', 'id_unit_kerja'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.sebaran_prodi');
    }
};
