<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\PeriodePendaftaran;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\SeleksiKomponen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.seleksi_komposisi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PeriodePendaftaran::class);
            $table->foreignIdTo(SeleksiJenis::class);
            $table->foreignIdTo(SeleksiKomponen::class);
            $table->decimal('persentase_komposisi', 5, 2, true);
            $table->logs(true);
        });

        SevimaSchema::table('pmb.seleksi_komposisi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_periode_pendaftaran', 'id_seleksi_jenis', 'id_seleksi_komponen'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.seleksi_komposisi');
    }
};
