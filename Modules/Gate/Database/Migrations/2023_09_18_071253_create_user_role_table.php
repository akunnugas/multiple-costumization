<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\UnitKerja;
use Modules\Gate\Models\Role;
use Modules\Gate\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('gate.user_role', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(User::class);
            $table->foreignIdTo(Role::class);
            $table->foreignIdTo(UnitKerja::class);
            $table->logs(true);
        });

        SevimaSchema::table('gate.user_role', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_user', 'id_role', 'id_unit_kerja'], true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.user_role');
    }
};
