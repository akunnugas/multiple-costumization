<?php

use Modules\Core\Extensions\SevimaBlueprint;
use Modules\Core\Extensions\SevimaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\DMS\Models\Dokumen;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\DosenEksternal;
use Modules\Litabmas\Models\PengajuanPendanaan;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SevimaSchema::create('litabmas.pengajuan_pendanaan', function (SevimaBlueprint $table) {
            $table->id();
            $table->string('judul_penelitian')->comment('Judul');
            $table->string('kode_registrasi')->nullable()->comment('ID Registrasi');    // di isi setelah proposal valid, by sistem
            $table->boolean('apakah_berkontribusi_bidang_ilmu')->default(false)
                ->comment('Apakah penelitian ini berkontribusi pada pengembangan keilmuan di Prodi?');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen_proposal', true);
            $table->timestampTz('waktu_snk_disetujui')->nullable()->comment('Waktu menyetujui Syarat dan Ketentuan');

            // terkait perduitan
            $table->string('mata_uang', 3)->default('IDR')->comment('Mata Uang');       // sesuai sumber klaster dan pendanaan
            $table->decimal('nominal_anggaran_diajukan', 15, 2)->nullable()->comment('Usulan Biaya');
            $table->decimal('nominal_anggaran_disetujui', 15, 2)->nullable()->comment('Biaya Disetujui');
            $table->decimal('nominal_anggaran_terpakai', 15, 2)->nullable()->comment('Biaya Terpakai');
            $table->decimal('persentase_anggaran_dicairkan', 5, 2)->nullable()
                ->comment('Status Biaya Dicairkan'); // dalam persen
            $table->foreignIdTo(Dokumen::class,'id_dokumen_rab', true);
            $table->string('nama_pemilik_rekening')->comment('Nama Pemilik Rekening');
            $table->string('nomor_rekening')->comment('Nomor Rekening');
            $table->string('nama_bank')->comment('Nama Bank');
            $table->string('cabang_bank')->nullable()->comment('Cabang Bank');
            $table->foreignIdTo(Dokumen::class,'id_foto_tabungan', true);

            // terkait relasinya
            $table->foreignIdTo(\Modules\Litabmas\Models\AgendaKegiatan::class, 'id_agenda_kegiatan', true);
            $table->string('kode_jenis_pendanaan', 25);
            $table->foreignIdTo(\Modules\Litabmas\Models\SumberPendanaan::class, 'id_sumber_pendanaan');
            $table->foreignIdTo( \Modules\Litabmas\Models\KlasterPendanaan::class, 'id_klaster_pendanaan');
            $table->foreignIdTo(\Modules\Litabmas\Models\BidangIlmu::class, 'id_bidang_ilmu');
            $table->foreignIdTo(\Modules\Litabmas\Models\TemaKegiatan::class, 'id_tema_kegiatan');

            // validasi dokumen proposal
            $table->boolean('apakah_proposal_valid')->default(false)
                ->comment('Apakah dokumen proposal valid?');
            $table->timestampTz('waktu_validasi_proposal')->nullable()
                ->comment('Waktu validasi dokumen proposal');
            $table->foreignIdTo(User::class,'proposal_valid_oleh', true);

            // validasi lolos nominasi
            $table->boolean('apakah_lolos_nominasi')->default(false)
                ->comment('Apakah lolos nominasi?');
            $table->timestampTz('waktu_lolos_nominasi')->nullable()
                ->comment('Waktu lolos nominasi');
            $table->foreignIdTo(User::class,'lolos_nominasi_oleh', true);

            // validasi lolos pendanaan
            $table->boolean('apakah_lolos_pendanaan')->default(false)
                ->comment('Apakah lolos pendanaan?');
            $table->timestampTz('waktu_lolos_pendanaan')->nullable()
                ->comment('Waktu lolos pendanaan');
            $table->foreignIdTo(User::class, 'lolos_pendanaan_oleh', true);

            // similarity dan ai
            $table->decimal('penilaian_index_similarity', 5, 2)->nullable()
                ->comment('Hasil Checking Similarity');
            $table->foreignIdTo(Dokumen::class, 'id_dokumen_penilaian_similarity', true);
            $table->decimal('penilaian_index_ai', 5, 2)->nullable()
                ->comment('Hascil Checking Artificial Intelligence');
            $table->foreignIdTo(Dokumen::class,'id_dokumen_penilaian_ai', true);

            // biar nggk berat harus selalu ngitung total skor dari sclae/skala, maka disimpan aja di proposalnya
            $table->decimal('total_penilaian_komposisi_proposal', 5, 2)->nullable()
                ->comment('Total Nilai Keseluruhan Aspek Komposisi Proposal'); // dari semua reviewer
            $table->decimal('total_penilaian_presentasi_proposal', 5, 2)->nullable()
                ->comment('Total Nilai Keseluruhan Aspek Presentasi Proposal'); // dari semua reviewer

            $table->logs();
        });

        // unique index composite utk table pengajuan_pendanaan (Pengajuan Pendanaan)
        DB::statement('CREATE UNIQUE INDEX ON litabmas.pengajuan_pendanaan (
            kode_jenis_pendanaan, id_sumber_pendanaan, id_klaster_pendanaan, id_tema_kegiatan, id_bidang_ilmu, judul_penelitian
        ) WHERE waktu_dihapus IS NULL');

        // Anggota dari Pengajuan Pendanaan
        SevimaSchema::create('litabmas.pengajuan_pendanaan_anggota', function (SevimaBlueprint $table) {
            $table->id();

            $table->foreignIdTo(PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->foreignIdTo(User::class, 'id_user'); // bisa mahasiswa, internal user ataupun external user
            $table->boolean('apakah_ketua')->default(false)->comment('Ketua');
            $table->unsignedTinyInteger('jenis_anggota')->comment('Jenis Anggota'); // bisa mahasiswa, internal user ataupun external user
            $table->foreignIdTo(DosenEksternal::class, 'id_dosen_eksternal', true); // utk kebutuhan history di pengajuan pendanaan
            $table->logs();
        });

        // unique index composite utk table pengajuan_pendanaan_anggota (Anggota Pengajuan Pendanaan)
        DB::statement('CREATE UNIQUE INDEX ON litabmas.pengajuan_pendanaan_anggota (
            id_pengajuan_pendanaan, id_user
        ) WHERE waktu_dihapus IS NULL');

        // Mapping Pengajuan Pendanaan dengan Aspek Penilaian -> Review Proposal
        SevimaSchema::create('litabmas.pengajuan_pendanaan_isian_proposal', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            $table->foreignIdTo(\Modules\Litabmas\Models\AspekPenilaianIsianProposal::class, 'id_aspek_penilaian_isian_proposal');
            $table->text('isian_proposal')->nullable()->comment('Isi Proposal');

            $table->logs();
        });

        // unique index composite utk table pengajuan_pendanaan_isian_proposal (Mapping Pengajuan Pendanaan dengan Aspek Penilaian -> Review Proposal)
        DB::statement('CREATE UNIQUE INDEX ON litabmas.pengajuan_pendanaan_isian_proposal (
            id_pengajuan_pendanaan, id_aspek_penilaian_isian_proposal
        ) WHERE waktu_dihapus IS NULL');

        // Mapping Pengajuan Pendanaan dengan Output
        SevimaSchema::create('litabmas.pengajuan_pendanaan_output_penelitian', function (SevimaBlueprint $table) {
            $table->id();
            $table->foreignIdTo(PengajuanPendanaan::class, 'id_pengajuan_pendanaan');
            // output kegiatan juga ngambil dari litabmas.klaster_pendanaan_output_penelitian buat dapetin yg WAJIB aja
            $table->foreignIdTo(\Modules\Litabmas\Models\JenisOutputPenelitian::class, 'id_jenis_output_penelitian');

            $table->logs();
        });

        // unique index composite utk table pengajuan_pendanaan_output_penelitian (Mapping Pengajuan Pendanaan dengan Output)
        DB::statement('CREATE UNIQUE INDEX ON litabmas.pengajuan_pendanaan_output_penelitian (
            id_pengajuan_pendanaan, id_jenis_output_penelitian
        ) WHERE waktu_dihapus IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_output_penelitian');
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_isian_proposal');
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan_anggota');
        SevimaSchema::dropIfExists('litabmas.pengajuan_pendanaan');
    }
};
