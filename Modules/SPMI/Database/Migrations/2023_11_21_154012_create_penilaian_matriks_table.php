<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Modules\SPMI\Models\AkreditasiStandar;
use Modules\SPMI\Models\PenilaianKlaster;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('spmi.penilaian_matriks', function (SevimaBlueprint $table) {
            $table->id();
            // Section Pertanyaan
            $table->foreignIdTo(PenilaianPanduan::class, 'id_penilaian_panduan');
            $table->foreignIdTo(PenilaianMatriks::class, 'id_parent', true);
            $table->string('nomor_penilaian', 15)->comment('No. Penilaian');
            $table->string('kategori_penilaian', 2)->comment('Kategori Matrix Penilaian (E: Element, D: Dimensi, I: Indikator)');
            $table->text('pertanyaan_penilaian')->comment('Pertanyaan Penilaian');
            $table->foreignIdTo(AkreditasiStandar::class, 'id_akreditasi_standar', true);
            $table->foreignIdTo(PenilaianKlaster::class, 'id_penilaian_klaster', true);
            $table->string('standar_perguruan_tinggi', 2)->nullable()->comment('Standart Pendidikan Tinggi (SN: SN-Dikti, RS: Target pada Rencana Strategis)');
            $table->string('syarat_terakreditasi', 2)->nullable()->comment('Syarat Perllu Akreditasi (T: Tidak, P: Peringkat Akreditasi, A: Terakreditasi)');
            $table->boolean('apakah_aktif')->comment('Status Penilaian')->default(true);

            // Section Penilaian
            $table->string('jenis_penilaian', 2)->nullable()->comment('Jenis Penilaian (IN: Kualitatif, PR: Kuantitatif, TL: Akumulasi Skor, SA: Skor Akhir)');
            $table->decimal('bobot_penilaian', 5, 2)->nullable()->comment('Bobot Penilaian');
            $table->string('referensi_penilaian', 2)->nullable()->comment('Sumber Referensi (BA: Laporan Kinerja, ED: Evaluasi Diri)');
            $table->text('rumus_penilaian')->nullable()->comment('rumus_penilaian');

            $table->text('deskripsi')->nullable()->comment('Keterangan');
            $table->boolean('apakah_nilai_ditampilkan')->nullable()->comment('Tampilkan hasil akhir')->default(false);
            $table->boolean('apakah_data_default')->nullable()->comment('IKU')->default(false);

            // tree
            $table->unsignedInteger('info_level')->comment('Level')->nullable();
            $table->integer('info_left')->comment('Info Left')->nullable();
            $table->integer('info_right')->comment('Info Right')->nullable();

            // index
            $table->index('info_left');
            $table->index('info_right');
            $table->logs(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('spmi.penilaian_matriks');
    }
};
