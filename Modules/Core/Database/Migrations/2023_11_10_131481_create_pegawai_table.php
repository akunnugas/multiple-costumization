<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\PerguruanTinggi;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\PegawaiManagementService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('core.pegawai', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Biodata::class);
            $table->foreignIdTo(UnitKerja::class, nullable: true);
            $table->string('nip', 25);
            $table->string('nip_pns', 25)->nullable();
            $table->string('email_kampus', 50)->nullable();
            $table->string('akun_sidik_jari', 25)->nullable();
            $table->string('ref_key_siakad')->nullable();
            $table->logs(true);
        });

        SevimaSchema::table('core.pegawai', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nip', true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('core.pegawai');
    }
};
