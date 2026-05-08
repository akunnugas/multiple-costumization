<?php

namespace Modules\SPMI\Models;

use Modules\Core\Extensions\Models\IndonesianModel;

class IndikatorCell extends IndonesianModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.indikator_cell';

    const LEFT = 'L';
    const CENTER = 'C';
    const RIGHT = 'R';

    const LABEL = 'L';
    const DISABLED = 'D';
    const SUM_COLUMN = 'J';
    const SUM_ALL = 'A';
    const AVERAGE = 'R';
    const COUNT_DATA_COLUMN = 'B';
    const COUNT_DATA_NOT_EMPTY_COLUMN = 'E';

    const CELL = 'C';
    const FOOTER = 'F';
    const SUBFOOTER = 'SF';

    const LABEL_POSITION = [
        self::LEFT => 'Left',
        self::CENTER => 'Center',
        self::RIGHT => 'Right',
    ];

    const CELL_TYPE = [
        self::LABEL => 'Label',
        self::DISABLED => 'Disabled',
    ];

    const FOOTER_ACTION = [
        self::LABEL => 'Label',
        self::DISABLED => 'Disabled',
        self::SUM_COLUMN => 'Sum Column',
        self::SUM_ALL => 'Sum All',
        self::AVERAGE => 'Average',
        self::COUNT_DATA_COLUMN => 'Count Data Column',
        self::COUNT_DATA_NOT_EMPTY_COLUMN => 'Count Data Not Empty Column'
    ];

    const CELL_CATEGORY = [
        self::CELL => 'Cell',
        self::FOOTER => 'Footer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'column_to',
        'row_to',
        'kategori_cell',
        'jenis_cell',
        'posisi_label',
        'id_indikator_laporan_kinerja',
        'nama',
        'colspan',
        'rowspan',
        'dapat_dilihat',
        'row_range_from',
        'row_range_to',
        'properti',
        'apakah_sub_footer'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'column_to' => ['required' => true, 'type' => 'integer'], // Kolom Ke
        'row_to' => ['required' => true, 'type' => 'integer'], // Baris Ke
        'kategori_cell' => ['required' => false, 'maxlength' => 255, 'options' => self::CELL_CATEGORY], // Kategori Sel (C: Cell, F: Footer)
        'jenis_cell' => ['required' => true, 'maxlength' => 255], // Tipe Sel (L: Label, D: Disabled, SC: SUM COLUMN , SA: SUM ALL, AVG: AVERAGE, SDK: SUM DATA COLUMN, SDNEC: SUM DATA NOT EMPTY COLUMN)
        'posisi_label' => ['required' => true, 'maxlength' => 255, 'options' => self::LABEL_POSITION], // Posisi Label (L: Left, C: Center, R: Right)
        'nama' => ['maxlength' => 255], // Nama Sel
        'colspan' => ['maxlength' => 255], // colspan
        'rowspan' => ['maxlength' => 255], // rowspan
        'dapat_dilihat' => ['type' => 'boolean'], // Tampilkan di laporan?
    ];
}
