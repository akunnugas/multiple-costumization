<?php

use Illuminate\Database\Migrations\Migration;
use Modules\SPMI\Models\IndikatorCell;
use Modules\SPMI\Models\IndikatorKolom;
use Modules\SPMI\Models\IndikatorLaporanKinerja;
use Modules\SPMI\Models\IndikatorBaris;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // butir 1.1a
        IndikatorLaporanKinerja::where('id', '2')->update([
            'apakah_subfooter' => 1,
        ]);
        IndikatorCell::whereIn('id', ['96', '97', '98', '99', '100', '101', '102', '103', '104', '105'])->update([
            'apakah_sub_footer' => 1,
        ]);

        // butir 3.a.3
        IndikatorKolom::whereIn('id', ['83', '84'])->update([
            'jenis_form' => IndikatorKolom::DECIMAL,
        ]);

        // butir 4.4
        $butir = IndikatorLaporanKinerja::find('27');
        $butir->apakah_menggunakan_kategori = true;
        $butir->apakah_layout_fixed = true;
        $butir->jenis_form = 'FC';
        $butir->apakah_subfooter = true;
        $butir->save();

        IndikatorKolom::where('id', '795')->update([
            'jenis_kolom' => IndikatorKolom::RERATASEMUDATA,
            'jenis_form' => IndikatorKolom::NUMBER,
            'parameter' => 'kolomawal::3;;kolomakhir::5',
        ]);
        IndikatorKolom::where('id', '799')->update([
            'jenis_kolom' => IndikatorKolom::RERATASEMUDATA,
            'jenis_form' => IndikatorKolom::NUMBER,
            'parameter' => 'kolomawal::7;;kolomakhir::9',
        ]);

        $row = IndikatorBaris::find('888');
        $row->row_range_from = 1;
        $row->row_range_to = 2;
        $row->save();

        IndikatorBaris::whereIn('id', ['888', '895', '896', '897', '898', '889'])->update([
            'row_range_from' => 1,
            'row_range_to' => 6,
        ]);

        IndikatorBaris::whereIn('id', ['890', '891'])->update([
            'row_range_from' => 7,
            'row_range_to' => 8,
        ]);

        IndikatorBaris::whereIn('id', ['892', '893', '894'])->update([
            'row_range_from' => 9,
            'row_range_to' => 11,
        ]);

        $footer = new IndikatorCell();
        $footer->column_to = 1;
        $footer->row_to = 1;
        $footer->id_indikator_laporan_kinerja = '27';
        $footer->kategori_cell = 'F';
        $footer->jenis_cell = 'L';
        $footer->posisi_label = 'L';
        $footer->nama = 'Jumlah';
        $footer->colspan = '2';
        $footer->rowspan = '1';
        $footer->apakah_sub_footer = true;
        $footer->dapat_dilihat = true;
        $footer->save();

        for ($i = 3; $i <= 10; $i++) {
            $footer = new IndikatorCell();
            $footer->column_to = $i;
            $footer->row_to = 1;
            $footer->id_indikator_laporan_kinerja = '27';
            $footer->kategori_cell = 'F';
            $footer->jenis_cell = 'J';
            $footer->posisi_label = 'L';
            $footer->colspan = '1';
            $footer->rowspan = '1';
            $footer->apakah_sub_footer = true;
            $footer->dapat_dilihat = true;
            $footer->save();
        }

        $footer = new IndikatorCell();
        $footer->column_to = 1;
        $footer->row_to = 1;
        $footer->id_indikator_laporan_kinerja = '27';
        $footer->kategori_cell = 'F';
        $footer->jenis_cell = 'L';
        $footer->posisi_label = 'L';
        $footer->nama = 'Total';
        $footer->colspan = '2';
        $footer->rowspan = '1';
        $footer->apakah_sub_footer = false;
        $footer->dapat_dilihat = true;
        $footer->save();

        for ($i = 3; $i <= 10; $i++) {
            $footer = new IndikatorCell();
            $footer->column_to = $i;
            $footer->row_to = 1;
            $footer->id_indikator_laporan_kinerja = '27';
            $footer->kategori_cell = 'F';
            $footer->jenis_cell = 'J';
            $footer->posisi_label = 'L';
            $footer->colspan = '1';
            $footer->rowspan = '1';
            $footer->apakah_sub_footer = false;
            $footer->dapat_dilihat = true;
            $footer->save();
        }

        $footer = IndikatorCell::find('111');
        $footer->properti = 'formula::5:7 + 5:8';
        $footer->save();

        // butir 2.1
        IndikatorCell::whereIn('id', ['139', '140'])->update([
            'properti' => 'validation_column::3'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
