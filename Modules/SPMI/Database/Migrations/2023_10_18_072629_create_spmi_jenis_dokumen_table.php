<?php

use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Extensions\SevimaBlueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\SpmiJenisDokumen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SevimaSchema::create('spmi.spmi_jenis_dokumen', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('nama_spmi_jenis_dokumen');
            $table->string('deskripsi')->nullable();
            $table->string('deskripsi_singkat')->nullable();
            $table->string('alamat_berkas')->nullable();

            $table->logs(true);
        });

        SpmiJenisDokumen::create([
            'nama_spmi_jenis_dokumen' => 'Kebijakan SPMI',
            'deskripsi_singkat' => 'Buatlah panduan kebijakan SPMI, sehingga semua pihak yang terlibat proses SPMI mudah untuk memahami, merancang, dan mengimplementasikan SPMI secara efektif',
            'alamat_berkas' => 'spmi-mutu-template/kebijakan_mutu_spmi.pdf'
        ]);

        SpmiJenisDokumen::create([
            'nama_spmi_jenis_dokumen' => 'Standar SPMI',
            'deskripsi_singkat' => 'Susunlah petunjuk teknis tentang implementasi PPEPP Standar Dikti agar dapat membantu seluruh pihak yang terlibat SPMI di Perguruan Tinggi Anda.',
            'alamat_berkas' => 'spmi-mutu-template/standar_mutu_spmi.pdf'
        ]);

        SpmiJenisDokumen::create([
            'nama_spmi_jenis_dokumen' => 'Manual SPMI',
            'deskripsi_singkat' => 'Rancang dan tetapkanlah berbagai standar SPMI, mulai dari kriteria, ukuran, dan spesifikasi untuk memastikan kualitas optimal dalam setiap kegiatan pendidikan tinggi.',
            'alamat_berkas' => 'spmi-mutu-template/manual_mutu_spmi.pdf'
        ]);

        SpmiJenisDokumen::create([
            'nama_spmi_jenis_dokumen' => 'Formulir SPMI',
            'deskripsi_singkat' => 'Tambahkan dan kelola kumpulan formulir SPMI yang dapat digunakan untuk mengimplementasikan standar dalam SPMI, sehingga dapat merekam dan mengelola informasi dengan mudah.',
            'alamat_berkas' => 'spmi-mutu-template/formulir_mutu_spmi.pdf'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SevimaSchema::dropIfExists('spmi.spmi_jenis_dokumen');
    }
};
