<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\ResourceAksi;
use Modules\Gate\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.role_akses_aksi', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Role::class);
            $table->foreignIdTo(ResourceAksi::class);
            $table->logs(false);
        });

        SevimaSchema::table('gate.role_akses_aksi', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_role', 'id_resource_aksi'], false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.role_akses_aksi');
    }
};
