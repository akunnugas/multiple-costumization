<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Gate\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::create([
            'kode_role' => 'addms',
            'nama_role' => 'Admin DMS',
            'apakah_statis' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::where('kode_role', 'addms')->delete();
    }
};
