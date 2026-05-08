<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
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
        SevimaSchema::create('spmi.indikator_kolom', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('id_indikator_laporan_kinerja');
            $table->enum('jenis_form', ['X','C','D','S','H','N','DC','A','B'])->nullable()->comment('X = Autocomplete | C = Checkbox | D = Date | S = Dropdown | H = Hidden | N = Number | DC = Decimal | A = Textarea | B = Textbox');
            $table->enum('jenis_kolom', ['I','B','J','R','A'])->comment('I = Pengisian | B = Bukan Pengisian | J = Jumlah | R = Rerata Data Tidak Kosong | A = Rerata Semua Data');
            $table->enum('posisi_kolom', ['H','V'])->nullable()->comment('H = Horizontal | V = Vertical');
            $table->string('properti')->nullable();
            $table->string('option_dropdown')->nullable();
            $table->boolean('apakah_terlihat')->default(true);
            $table->integer('colspan')->default(1);
            $table->integer('rowspan')->default(1);
            $table->string('parameter')->nullable();

            // foreign key
            $table->foreign('id_indikator_laporan_kinerja')->references('id')->on('spmi.indikator_laporan_kinerja');
            $table->foreign('id_parent')->references('id')->on('spmi.indikator_kolom');

            // tree structure
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->unsignedInteger('info_level')->nullable();
            $table->integer('info_left')->nullable();
            $table->integer('info_right')->nullable();

            // create index
            $table->index(['id_indikator_laporan_kinerja','id_parent','info_left','info_right']);

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
        SevimaSchema::dropIfExists('spmi.indikator_kolom');
    }
};
