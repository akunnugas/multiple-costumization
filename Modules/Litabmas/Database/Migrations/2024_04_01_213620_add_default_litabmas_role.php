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
        \Illuminate\Support\Facades\DB::beginTransaction();

        Role::updateOrCreate([
            'kode_role' => 'lm_peneliti',
        ], [
            'nama_role' => 'Peneliti',
            'kode_role' => 'lm_peneliti',
            'apakah_statis' => true
        ]);

        Role::updateOrCreate([
            'kode_role' => 'lm_pembimbing',
        ], [
            'nama_role' => 'Pembimbing',
            'kode_role' => 'lm_pembimbing',
            'apakah_statis' => true
        ]);

        Role::updateOrCreate([
            'kode_role' => 'lm_reviewer',
        ], [
            'nama_role' => 'Reviewer',
            'kode_role' => 'lm_reviewer',
            'apakah_statis' => true
        ]);

        Role::updateOrCreate([
            'kode_role' => Role::ROLE_LITABMAS_ADMIN_LPPM,
        ], [
            'nama_role' => 'Admin LPPM',
            'kode_role' => Role::ROLE_LITABMAS_ADMIN_LPPM,
            'apakah_statis' => true
        ]);

        Role::updateOrCreate([
            'kode_role' => Role::ROLE_LITABMAS_KETUA_LPPM,
        ], [
            'nama_role' => 'Ketua LPPM',
            'kode_role' => Role::ROLE_LITABMAS_KETUA_LPPM,
            'apakah_statis' => true
        ]);

        \Illuminate\Support\Facades\DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::beginTransaction();

        Role::where('kode_role', 'lm_peneliti')->delete();
        Role::where('kode_role', 'lm_pembimbing')->delete();
        Role::where('kode_role', 'lm_reviewer')->delete();
        Role::where('kode_role', Role::ROLE_LITABMAS_ADMIN_LPPM)->delete();
        Role::where('kode_role', Role::ROLE_LITABMAS_KETUA_LPPM)->delete();

        \Illuminate\Support\Facades\DB::commit();
    }
};
