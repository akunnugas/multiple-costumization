<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $idsPanduan = DB::table('spmi.pengisian_panduan')
            ->where('waktu_dihapus', '!=', null)
            ->pluck('id')
            ->toArray();

        DB::table('spmi.data_pengisian_lk')
            ->join('spmi.indikator_laporan_kinerja', 'spmi.data_pengisian_lk.id_indikator_laporan_kinerja', '=', 'spmi.indikator_laporan_kinerja.id')
            ->whereIn('spmi.indikator_laporan_kinerja.id_pengisian_panduan', $idsPanduan)
            ->delete();

        DB::table('spmi.data_pengisian_led')
            ->join('spmi.indikator_evaluasi_diri', 'spmi.data_pengisian_led.id_indikator_evaluasi_diri', '=', 'spmi.indikator_evaluasi_diri.id')
            ->whereIn('spmi.indikator_evaluasi_diri.id_pengisian_panduan', $idsPanduan)
            ->delete();

        DB::table('spmi.spmi_tarikdata_lk')
            ->join('spmi.indikator_laporan_kinerja', 'spmi.spmi_tarikdata_lk.id_indikator_laporan_kinerja', '=', 'spmi.indikator_laporan_kinerja.id')
            ->whereIn('spmi.indikator_laporan_kinerja.id_pengisian_panduan', $idsPanduan)
            ->delete();

        DB::table('spmi.pengisian_indikator')
            ->whereIn('id_pengisian_panduan', $idsPanduan)
            ->delete();

        DB::table('spmi.indikator_laporan_kinerja')
            ->whereIn('id_pengisian_panduan', $idsPanduan)
            ->delete();

        DB::table('spmi.indikator_evaluasi_diri')
            ->whereIn('id_pengisian_panduan', $idsPanduan)
            ->delete();

        DB::table('spmi.pengisian_panduan')
            ->whereIn('id', $idsPanduan)
            ->delete();

        DB::table('spmi.akreditasi_buku')
            ->whereNotIn('kode_buku', ['LKPS', 'LED'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
