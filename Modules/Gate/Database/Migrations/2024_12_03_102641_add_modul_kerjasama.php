<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Gate\Models\Modul;
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
        Modul::create([
            'nama_modul' => 'Kerjasama',
            'kode_modul' => Modul::CODE_KERJASAMA,
            'apakah_aktif' => true
        ]);

        Role::updateOrCreate(
            ['kode_role' => Role::ROLE_ADMIN_KERJASAMA],
            [
                'nama_role' => 'Admin Kerjasama',
                'kode_role' => Role::ROLE_ADMIN_KERJASAMA,
                'ref_key_siakad' => 'ADMKS',
                'apakah_statis' => true
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
