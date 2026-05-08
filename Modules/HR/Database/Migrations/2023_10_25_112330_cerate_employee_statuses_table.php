<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('hr.employee_statuses', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('code', 5)->comment('Kode');
            $table->string('name')->comment('Nama Status Aktif');
            $table->boolean('is_active')->default(true)->comment('Status');
            $table->string('emis_code')->nullable()->comment('Kode Emis');
            $table->string('ref_key_siakad')->nullable()->comment('Kolom PK SIAKAD V1');

            $table->logs(true);
        });

        DB::statement('CREATE UNIQUE INDEX ON hr.employee_statuses (code) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('hr.employee_statuses');
    }
};
