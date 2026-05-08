<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Models\PerguruanTinggi;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('core.program_studi', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('kode_prodi', 20);
            $table->string('nama_prodi', 100);
            $table->string('alamat_prodi', 100)->nullable();
            $table->string('telepon_prodi', 20)->nullable();
            $table->foreignIdTo(PerguruanTinggi::class);
            $table->foreignIdTo(JenjangPendidikan::class);
            $table->string('kode_sister')->nullable();
            $table->logs();
            $table->index('kode_prodi');
            $table->index('nama_prodi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('core.program_studi');
    }
};
