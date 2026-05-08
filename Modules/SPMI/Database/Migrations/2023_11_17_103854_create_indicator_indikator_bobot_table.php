<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.indikator_bobot', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignId('id_audit_periode')->comment('ID Periode Audit')
                ->constrained('spmi.audit_periode');
            $table->string('jenis_indikator_bobot', 10)->comment('Tipe Kategori Indikator');
            $table->string('nama_kategori_indikator')->comment('Nama Kategori Indikator');
            $table->decimal('persentase', 5, 2)->comment('Persentase Indikator')->default(0.00);

            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.indikator_bobot');
    }
};
