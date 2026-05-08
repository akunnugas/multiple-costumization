<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\JenisInstitusi;
use Modules\Core\Models\Wilayah;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.sekolah', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_sekolah');
            $table->string('npsn');
            $table->string('alamat_sekolah');
            $table->string('rt_sekolah')->nullable();
            $table->string('rw_sekolah')->nullable();
            $table->string('kode_pos_sekolah')->nullable();
            $table->foreignIdTo(Wilayah::class, 'id_kota');
            $table->string('telepon_sekolah')->nullable();
            $table->string('email_sekolah')->nullable();
            $table->string('website_sekolah')->nullable();
            $table->foreignIdTo(JenisInstitusi::class);
            $table->string('akreditasi')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.sekolah', function (SevimaBlueprint $table) {
            $table->uniqueIndex('npsn', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.sekolah');
    }
};
