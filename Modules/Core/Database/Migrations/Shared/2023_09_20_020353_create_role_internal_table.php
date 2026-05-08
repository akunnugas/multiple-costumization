<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('role_internal', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_role');
            $table->string('kode_role', 100);
            $table->integer('level_cp')->default(0);
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('role_internal', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_role', true);
            $table->uniqueIndex('kode_role', true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('role_internal');
    }
};
