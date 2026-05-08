<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\Resource;
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
        SevimaSchema::create('gate.role_akses', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Role::class);
            $table->foreignIdTo(Resource::class);
            $table->boolean('bisa_get');
            $table->boolean('bisa_post');
            $table->boolean('bisa_put');
            $table->boolean('bisa_delete');
            $table->logs(false);
        });

        SevimaSchema::table('gate.role_akses', function (SevimaBlueprint $table) {
            $table->uniqueIndex(['id_role', 'id_resource'], false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('gate.role_akses');
    }
};
