<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\UnitKerjaManagementService;
use Modules\Gate\Models\UserRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "UPDATE core.pegawai p
                SET id_unit_kerja = id_unit_asli
            FROM (
                SELECT u.id id_template, uk.id id_unit_asli
                FROM core.unit_kerja u
                JOIN core.unit_kerja uk on true
                WHERE u.kode_unit = '0000'
                    AND (uk.kode_unit != '0000' and uk.jenis_unit = 'U')
            ) x
            WHERE p.id_unit_kerja = x.id_template;"
        );

        // Clear user role
        UserRole::truncate();
    }

    /**
 * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
