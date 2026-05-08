<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Pendaftar;
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
        SevimaSchema::create('pmb.pilihan_prodi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Pendaftar::class);
            $table->foreignIdTo(SebaranProdi::class);
            $table->integer('urutan_pilihan')->nullable();
            $table->string('status_pilihan')->nullable();
            $table->float('nilai_pilihan')->nullable();
            $table->boolean('apakah_rekomendasi')->default(0);
            $table->boolean('apakah_afirmasi')->default(0);
            $table->boolean('apakah_afirmasi_disetujui')->default(0);
            $table->foreignIdTo(User::class, 'afirmasi_disetujui_oleh', true);
            $table->logs(true);
        });

        SevimaSchema::table('pmb.pilihan_prodi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_pendaftar', 'id_sebaran_prodi'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('pmb.pilihan_prodi');
    }
};
