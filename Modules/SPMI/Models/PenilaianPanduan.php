<?php

namespace Modules\SPMI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\JenjangPendidikan;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\JenisPerguruanTinggi;
use Modules\DMS\Models\Dokumen;

class PenilaianPanduan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'kode_penilaian_panduan asc';
    const OPTION_COLUMN = 'nama_singkat';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spmi.penilaian_panduan';

    protected $fillable = [
        'kode_penilaian_panduan',
        'nama_penilaian_panduan',
        'nama_singkat',
        'id_laporan_kinerja',
        'id_panduan_evaluasi_diri',
        'tanggal_edisi',
        'id_jenjang_pendidikan',
        'id_jenis_perguruan_tinggi',
        'id_jenis_standar',
        'deskripsi',
        'apakah_ptn',
        'apakah_aktif',
        'apakah_target_aktif',
        'dapat_lihat_skor_akhir',
        'id_dokumen',
        'total_indikator_matriks',
        'apakah_data_default',
        'apakah_menggunakan_peringkat',
        'apakah_iku_kualitatif'
    ];

    const DISABLE_EDIT_SKOR_PANDUAN = [
        'IAPS5.1-S1-Akre',
        'IAPS5.1-S1-Ung',
        'IAPS5.1-S2-Akre',
        'IAPS5.1-S2-Ung',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_penilaian_panduan' => ['required' => true, 'maxlength' => 15],
        'nama_penilaian_panduan' => ['required' => true, 'maxlength' => 255],
        'nama_singkat' => ['required' => true],
        'id_laporan_kinerja' => ['required' => true],
        'id_panduan_evaluasi_diri' => ['required' => false],
        'tanggal_edisi' => ['required' => false, 'type' => 'date'],
        'id_jenjang_pendidikan' => ['required' => true, 'options' => JenjangPendidikan::class],
        'id_jenis_standar' => ['required' => true, 'options' => JenisStandar::class],
        'id_jenis_perguruan_tinggi' => ['required' => false, 'options' => JenisPerguruanTinggi::class],
        'deskripsi' => ['required' => false, 'maxlength' => 255],
        'apakah_ptn' => ['required' => false, 'boolean' => true],
        'apakah_aktif' => ['required' => false, 'boolean' => true],
        'apakah_target_aktif' => ['required' => false, 'boolean' => true],
        'dapat_lihat_skor_akhir' => ['required' => false, 'boolean' => true],
        'total_indikator_matriks' => ['required' => false],
        'apakah_iku_kualitatif' => ['required' => false, 'boolean' => true],
        'id_dokumen' => [
            'required' => false, 'file_type' => Dokumen::TYPE_DOCUMENT, 'max_size' => 5024
        ], // ID Dokumen
    ];

    protected static function newFactory()
    {
        return \Modules\SPMI\Database\factories\PenilaianPanduanFactory::new();
    }

    public static function optionsByIds(array $ids, bool $isOnlyDataDefault = false)
    {
        $orderColumn = static::OPTION_ORDER;

        return static::whereIn('id', $ids)
            ->orderByRaw($orderColumn)
            ->when($isOnlyDataDefault, function ($query) {
                $query->where('apakah_data_default', true);
            })
            ->get(['id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    public static function optionsDefault()
    {
        $orderColumn = static::OPTION_ORDER;

        return static::where('apakah_data_default', true)
            ->where('apakah_aktif', true)
            ->orderByRaw($orderColumn)
            ->get(['id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }
}
