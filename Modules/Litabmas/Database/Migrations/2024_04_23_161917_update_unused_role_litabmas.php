<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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

        // [Start] Remove unused role
        $unusedRoleIds = Role::whereIn('kode_role', [
            'lm_peneliti',
            'lm_pembimbing',
            'lm_reviewer',
        ])->pluck('id');

        // remove user role
        DB::table('gate.user_role')
            ->whereIn('id_role', $unusedRoleIds)
            ->delete();

        // remove role akses
        DB::table('gate.role_akses')
            ->whereIn('id_role', $unusedRoleIds)
            ->delete();

        // remove role
        DB::table('gate.role')
            ->whereIn('id', $unusedRoleIds)
            ->delete();
        // [End] Remove unused role

        // [Start] Add role ke table role jika belum ada
        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_REKTOR],
            [
                'nama_role' => 'Rektor',
                'apakah_statis' => true
            ]
        );
        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_DEKAN],
            [
                'nama_role' => 'Dekan',
                'apakah_statis' => true
            ]
        );
        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_DOSEN],
            [
                'nama_role' => 'Dosen',
                'apakah_statis' => true
            ]
        );
        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_DOSEN_EKSTERNAL],
            [
                'nama_role' => 'Dosen Eksternal',
                'apakah_statis' => true
            ]
        );
        // [End] Add role ke table role jika belum ada

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

        \Illuminate\Support\Facades\DB::commit();
    }
};
