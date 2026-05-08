<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Litabmas\Models\JenisOutputPenelitian;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::table('litabmas.jenis_output_penelitian', function (SevimaBlueprint $table) {
            $table->dropColumn('id_jenis_publikasi');
            $table->dropColumn('kategori_output');

            $table->string('nama_output')->nullable();
        });

        SevimaSchema::table('litabmas.jenis_output_penelitian', function (SevimaBlueprint $table) {
            $table->uniqueIndex('nama_output');
        });

        // default data
        $data = [
            'Laporan Penelitian', 'Draft Buku Ajar', 'Draft Buku', 'Draft Artikel', 'Dokumen Feasibility',
            'Dokumen Business Plan', 'Policy Brief, Rekomendasi Kebijakan, Model Kebijakan Strategis'
        ];

        foreach ($data as $namaOutput) {
            JenisOutputPenelitian::updateOrCreate([
                'nama_output' => $namaOutput
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::table('litabmas.jenis_output_penelitian', function (SevimaBlueprint $table) {
            $table->dropColumn('nama_output');

            $table->foreignIdTo(\Modules\Litabmas\Models\JenisPublikasi::class, nullable: true);
            $table->string('kategori_output', 2)->nullable();
        });

        SevimaSchema::table('litabmas.jenis_output_penelitian', function (SevimaBlueprint $table) {
            $table->uniqueIndex('id_jenis_publikasi');
        });
    }
};
