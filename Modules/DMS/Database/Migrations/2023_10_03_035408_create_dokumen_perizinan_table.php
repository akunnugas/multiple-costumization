<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\DMS\Models\Dokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('dms.dokumen_perizinan', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(Dokumen::class, "id_dokumen");
            $table->string('nama_perizinan', 100)->comment('Nama Perizinan');
            $table->char('jenis_perizinan', 1)->default('O')->comment('Jenis Perizinan (C: collaborator, O: organization)'); // tipe defaultnya unit / organisasi (O)
            $table->logs(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('dms.dokumen_perizinan');
    }
};
