<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\PMB\Models\SeleksiJenis;
use Modules\PMB\Models\SebaranProdi;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('pmb.seleksi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(SebaranProdi::class);
            $table->foreignIdTo(SeleksiJenis::class);
            $table->integer('urutan_seleksi')->nullable();
            $table->decimal('persentase_nilai', 5, 2, true)->nullable();
            $table->timestampTz('waktu_mulai')->nullable();
            $table->timestampTz('waktu_selesai')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('pmb.seleksi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_sebaran_prodi', 'id_seleksi_jenis'], true);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.seleksi');
    }
};
