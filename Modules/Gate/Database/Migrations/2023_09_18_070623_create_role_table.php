<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\Role;
use Modules\Gate\Services\RoleManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.role', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_role');
            $table->string('kode_role', 100);
            $table->boolean('apakah_statis');
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('gate.role', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_role', true);
            $table->uniqueIndex('kode_role', true);
        });

        // syncronize with roles on v1
        $roles = new RoleManagementService;
        $roles->syncFromSiakadv1();

        // create or update role
        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_ADMINPT],
            ['nama_role' => 'Administrator PT', 'apakah_statis' => false]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.role');
    }
};
