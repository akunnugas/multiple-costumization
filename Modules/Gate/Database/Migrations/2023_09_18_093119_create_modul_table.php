<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\Modul;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.modul', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_modul');
            $table->string('kode_modul', 20);
            $table->boolean('apakah_aktif');
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->unsignedInteger('info_level')->nullable();
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();
            $table->logs(true);
            $table->index('id_parent');
            $table->index('info_left');
            $table->index('info_right');
        });

        SevimaSchema::table('gate.modul', function (SevimaBlueprint $table) {
            $table->foreign('id_parent')->references('id')->on('gate.modul');
            $table->uniqueIndex('kode_modul', true);
        });

        Modul::create([
            'nama_modul' => 'Administrasi Aplikasi',
            'kode_modul' => Modul::CODE_ADMIN,
            'apakah_aktif' => true
        ]);

        Modul::create([
            'nama_modul' => 'Sistem Manajemen Dokumen',
            'kode_modul' => Modul::CODE_DMS,
            'apakah_aktif' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.modul');
    }
};
