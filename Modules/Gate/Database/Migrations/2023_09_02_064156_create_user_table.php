<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.user', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_user');
            $table->string('email_user')->nullable();
            $table->timestampTz('waktu_verifikasi_email')->nullable();
            $table->string('telepon_user', 20)->nullable();
            $table->unsignedBigInteger('id_user_sso')->nullable();
            $table->unsignedBigInteger('id_undangan_sso')->nullable();
            $table->timestampTz('waktu_undangan_sso')->nullable();
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('gate.user', function (SevimaBlueprint $table) {
            $table->uniqueIndex('email_user', true);
            $table->uniqueIndex('telepon_user', true);
            $table->uniqueIndex('id_user_sso', true);
        });

        DB::statement("SELECT SETVAL('gate.user_id_seq', 1001, FALSE)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.user');
    }
};
