<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\TreeStructure;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndikatorKolom extends IndonesianModel
{
    use HasFactory, TreeStructure, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.indikator_kolom';

    const AUTOCOMPLETE = 'X';
    const CHECKBOX = 'C';
    const DATE = 'D';
    const DROPDOWN = 'S';
    const HIDDEN = 'H';
    const NUMBER = 'N';
    const DECIMAL = 'DC';
    const TEXTAREA = 'A';
    const TEXT_BOX = 'B';
    const VERTICAL = 'V';
    const HORIZONTAL = 'H';

    const PENGISIAN = 'I';
    const BUKANPENGISIAN = 'B';
    const JUMLAH = 'J';
    const RERATADATATIDAKKOSONG = 'R';
    const RERATASEMUDATA = 'A';

    const DRPROPERTIES = [
        'getTingkatSertifikasi' => 'Get Tingkat Sertifikasi',
        'getLingkupSertifikasi' => 'Get Lingkup Sertifikasi',
        'getDosenTetap' => 'Get Dosen Tetap',
        'getUnit' => 'Get Unit',
        'getAllProdi' => 'Get All Prodi',
        'getFakultasProdi' => 'Get Fakultas Prodi',
        'getNonProdi' => 'Get Non Prodi',
        'getJabatanAkademik' => 'Get Jabatan Akademik',
        'getAdaTidak' => 'Get Ada Tidak',
        'getTersediaTidak' => 'Get Tersedia Tidak',
        'getStatusAkreditasi' => 'Get Status Akreditasi',
    ];


    const FORM_TYPE = [
        self::CHECKBOX => 'Checkbox',
        self::DATE => 'Date',
        self::DROPDOWN => 'Dropdown',
        self::NUMBER => 'Number',
        self::DECIMAL => 'Decimal',
        self::TEXTAREA => 'Textarea',
        self::TEXT_BOX => 'Text Box',
        self::HIDDEN => 'Hidden',
    ];

    const LAYOUT_TYPE = [
        self::HORIZONTAL => 'Horizontal',
        self::VERTICAL => 'Vertical',
    ];

    const COLUMN_TYPE = [
        self::PENGISIAN => 'Pengisian',
        self::BUKANPENGISIAN => 'Bukan Pengisian',
        self::JUMLAH => 'Jumlah',
        self::RERATADATATIDAKKOSONG => 'Rerata Data Tidak Kosong',
        self::RERATASEMUDATA => 'Rerata Semua Data',
    ];

    protected $fillable = [
        'nama',
        'ref_key_akreditasi',
        'id_indikator_laporan_kinerja',
        'jenis_form',
        'jenis_kolom',
        'posisi_kolom',
        'apakah_terlihat',
        'id_parent',
        'colspan',
        'info_level',
        'info_left',
        'info_right',
        'rowspan',
        'properti',
        'parameter',
        'option_dropdown',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama' => ['required' => true],
        'id_parent' => ['required' => false],
        'id_indikator_laporan_kinerja' => ['required' => false],
        'jenis_form' => ['required' => false, 'options' => self::FORM_TYPE],
        'jenis_kolom' => ['required' => true, 'options' => self::COLUMN_TYPE],
        'posisi_kolom' => ['required' => true, 'options' => self::LAYOUT_TYPE],
        'colspan' => ['required' => false],
        'rowspan' => ['required' => false],
        'properti' => ['required' => false, 'options' => self::DRPROPERTIES],
        'parameter' => ['required' => false],
        'option_dropdown' => ['required' => false],
        'apakah_terlihat' => ['required' => false, 'boolean' => true],
    ];

    /**
     * Get the indicator performance reports that owns the IndikatorKolom
     *
     * @return array
     */
    public static function optionsIndicatorColumn(int $id)
    {
        $data = self::getListComboTree(['id_indikator_laporan_kinerja' => $id]);

        return $data;
    }

    /**
     * Get list of combo tree
     *
     * @param array $condition
     * @return array
     */
    protected static function getListComboTree($condition = [])
    {
        $class = static::class;

        if (!empty($condition)) {
            $list = $class::where($condition)->orderBy('info_left','asc')->get();
        } else {
            $list = $class::orderBy('info_left', 'asc')->get();
        }

        // add prefix to name
        $list = $list->map(function ($item, $key) {
            $item->nama = str_repeat('&nbsp;', $item->info_level * 4) . $item->nama;
            return $item;
        });

        return $list->pluck('nama', 'id')->toArray();
    }

    protected static function defineDepthField()
    {
        return 'info_level';
    }

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\IndikatorKolomFactory::new();
    }
}
