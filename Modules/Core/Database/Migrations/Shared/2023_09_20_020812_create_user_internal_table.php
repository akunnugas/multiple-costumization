<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Shared\RoleInternal;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('user_internal', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_user');
            $table->string('email_user')->nullable();
            $table->timestampTz('waktu_verifikasi_email')->nullable();
            $table->string('telepon_user', 20)->nullable();
            $table->unsignedBigInteger('id_user_sso')->nullable();
            $table->foreignIdTo(RoleInternal::class);
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('user_internal');
    }
};
