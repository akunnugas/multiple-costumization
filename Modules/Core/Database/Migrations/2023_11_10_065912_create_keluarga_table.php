<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\Pekerjaan;
use Modules\Core\Models\Penghasilan;
use Modules\Core\Models\StatusHubunganKeluarga;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.keluarga', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Biodata::class);
            $table->string('kode_keluarga');
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->char('jenis_kelamin', 1)->nullable();
            $table->string('nik')->nullable();
            $table->foreignIdTo(StatusHubunganKeluarga::class);
            $table->foreignIdTo(JenjangPendidikan::class, nullable: true);
            $table->foreignIdTo(Pekerjaan::class, nullable: true);
            $table->foreignIdTo(Penghasilan::class, nullable: true);
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.keluarga');
    }
};
