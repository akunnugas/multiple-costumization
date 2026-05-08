<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\LembagaAkreditasi;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class PengisianPanduan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'kode_pengisian_panduan asc';
    const OPTION_COLUMN = 'nama_singkat';

    const LEVEL_AKSES_PS = 'PS';
    const LEVEL_AKSES_PT = 'PT';
    const LEVEL_AKSES_UPPS = 'UPPS';

    const LIST_LEVEL_AKSES = [
        self::LEVEL_AKSES_PS => 'Program Studi',
        self::LEVEL_AKSES_PT => 'Perguruan Tinggi',
        self::LEVEL_AKSES_UPPS => 'Unit Pengelola Program Studi',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.pengisian_panduan';

    protected $fillable = [
        'kode_pengisian_panduan',
        'nama_pengisian_panduan',
        'nama_singkat',
        'id_jenjang_pendidikan',
        'id_lembaga_akreditasi',
        'kode_level_akses',
        'id_akreditasi_buku',
        'id_pengisian_panduan',
        'id_dokumen',
        'tipe_edisi',
        'deskripsi',
        'apakah_aktif',
        'tanggal_edisi',
        'tanggal_efektif',
        'tanggal_kadaluwarsa',
        'apakah_sapto',
        'apakah_data_default',
        'apakah_iku_kualitatif'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_pengisian_panduan' => ['required' => true, 'maxlength' => 10],
        'nama_pengisian_panduan' => ['required' => true, 'maxlength' => 255],
        'nama_singkat' => ['required' => true, 'maxlength' => 255],
        // 'kode_level_akses' => ['required' => true, 'options' => self::LIST_LEVEL_AKSES],
        'id_akreditasi_buku' => ['required' => true, 'options' => AkreditasiBuku::class],
        'id_pengisian_panduan' => ['required' => false],
        'id_jenjang_pendidikan' => ['required' => false, 'options' => JenjangPendidikan::class],
        'id_lembaga_akreditasi' => ['required' => true, 'options' => LembagaAkreditasi::class],
        'id_dokumen' => [
            'required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024
        ], // ID Dokumen
        'tipe_edisi' => ['required' => true, 'maxlength' => 2, 'options' => AkreditasiBuku::TYPE],
        'deskripsi' => ['required' => false, 'maxlength' => 255],
        'apakah_aktif' => ['required' => false, 'boolean' => true],
        'tanggal_edisi' => ['required' => false, 'type' => 'date'],
        'tanggal_efektif' => ['required' => false, 'type' => 'date'],
        'tanggal_kadaluwarsa' => ['required' => false, 'type' => 'date'],
        'apakah_sapto' => ['required' => false, 'boolean' => true],
        'apakah_iku_kualitatif' => ['required' => false, 'boolean' => true],
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\PengisianPanduanFactory::new();
    }

    public static function getListSelfEvaluation()
    {
        return self::where('tipe_edisi', AkreditasiBuku::SELF_EVALUATION)
            // ->when(!empty($filter), function ($query) use ($filter) {
            //     return $query->where($filter);
            // })
            ->where('apakah_aktif', true)
            ->get()->pluck('nama_singkat', 'id')->toArray();
    }

    public static function getListDefaultSelfEvaluation()
    {
        return self::where('tipe_edisi', AkreditasiBuku::SELF_EVALUATION)
            ->where('apakah_data_default', true)
            ->where('apakah_aktif', true)
            ->get()->pluck('nama_singkat', 'id')->toArray();
    }

    public static function getFristSelfEvaluation()
    {
        return self::where('tipe_edisi', AkreditasiBuku::SELF_EVALUATION)->first();
    }

    // FIXME : saat ini masih untuk BAN PT saja
    public static function getIAPSIndicator()
    {
        return self::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)->where('kode_pengisian_panduan','IAPS9')->first();
    }

    public static function getListIndicatorPerformanceReport()
    {
        return self::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)
            // ->when(!empty($filter), function ($query) use ($filter) {
            //     return $query->where($filter);
            // })
            ->where('apakah_aktif', true)
            ->get()->pluck('nama_singkat', 'id')->toArray();
    }

    public static function getListDefaultIndicatorPerformanceReport()
    {
        return self::where('tipe_edisi', AkreditasiBuku::PERFORMANCE_REPORT)
            ->where('apakah_data_default', true)
            ->where('apakah_aktif', true)
            ->get()->pluck('nama_singkat', 'id')->toArray();
    }

    public static function getRefrenceSelfEvaluation($id)
    {
        return self::where('id_pengisian_panduan', $id)->get()->pluck('nama_singkat', 'id')->toArray();
    }

    public static function findFirstRefrence($id)
    {
        return self::where('id_pengisian_panduan', $id)->first();
    }

    public static function getListShortName()
    {
        return self::get()->pluck('nama_singkat', 'id')->toArray();
    }

}
