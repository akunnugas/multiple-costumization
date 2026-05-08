<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Pegawai;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->dropColumn('id_user');
            $table->dropColumn('id_pegawai');

            $table->foreignIdTo(Biodata::class,nullable: true);
            $table->string('nip', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->dropColumn('id_biodata');
            $table->dropColumn('nip');

            $table->foreignIdTo(\Modules\Gate\Models\User::class, 'id_user', nullable: true);
            $table->foreignIdTo(Pegawai::class, 'id_pegawai', nullable: true);
        });

        SevimaSchema::table('litabmas.dosen_eksternal', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_user']);
            $table->uniqueIndex(['id_pegawai']);
        });
    }
};
