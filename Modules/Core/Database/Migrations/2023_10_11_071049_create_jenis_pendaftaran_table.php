<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\JenisPendaftaran;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.jenis_pendaftaran', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_jenis_pendaftaran', 10);
            $table->string('nama_jenis_pendaftaran');
            $table->date('tanggal_awal_pendaftaran')->nullable();
            $table->date('tanggal_akhir_pendaftaran')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.jenis_pendaftaran', function (SevimaBlueprint $table) {
            $table->uniqueIndex('kode_jenis_pendaftaran', true);
        });

        // SET DEFAULT DATA
        $codes = JenisPendaftaran::CODE_LISTS;
        foreach ($codes as $code => $name) {
            $endDate = null;

            if ($code == JenisPendaftaran::CODE_ALIHJENJANG || $code == JenisPendaftaran::CODE_LINTASJALUR) {
                $endDate = '2022-07-31';
            }

            JenisPendaftaran::create([
                'kode_jenis_pendaftaran' => $code,
                'nama_jenis_pendaftaran' => $name,
                'tanggal_akhir_pendaftaran' => $endDate
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.jenis_pendaftaran');
    }
};
