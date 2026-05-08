<?php

use Illuminate\Database\Migrations\Migration;
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
        Role::where('kode_role', 'admpt')->update(['kode_role' => Role::ROLE_ADMINPT]);
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Role::where('kode_role', Role::ROLE_ADMINPT)->update(['kode_role' => 'admpt']);
    }
};
