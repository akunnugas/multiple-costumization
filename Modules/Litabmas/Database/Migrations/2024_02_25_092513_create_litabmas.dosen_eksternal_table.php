<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\PerguruanTinggi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(\Modules\Gate\Models\User::class, 'id_user');
            $table->foreignIdTo(\Modules\Core\Models\Pegawai::class, 'id_pegawai');
            $table->foreignIdTo(\Modules\Gate\Models\User::class, 'id_pengusul', nullable: true);
            $table->foreignIdTo(PerguruanTinggi::class, 'id_perguruan_tinggi_luar');
            $table->char('status_usulan', 2)->comment('Status Usulan');

            $table->logs();
        });

        // deUserPerguruanTinggi
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_user']);
            $table->uniqueIndex(['id_pegawai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.dosen_eksternal');
    }
};
