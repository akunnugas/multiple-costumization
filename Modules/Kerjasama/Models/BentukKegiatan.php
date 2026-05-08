<?php

namespace Modules\Kerjasama\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class BentukKegiatan extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'nama_bentuk_kegiatan asc';
    const OPTION_COLUMN = 'nama_bentuk_kegiatan';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.bentuk_kegiatan';

    protected $fillable = [
        'id_jenis_kegiatan',
        'nama_bentuk_kegiatan',
        'keterangan',
        'isian_default'
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_jenis_kegiatan' => ['required' => true, 'options' => JenisKegiatan::class, 'control' => 'select', 'variant' => 'search'],
        'nama_bentuk_kegiatan' => ['required' => true, 'maxlength' => 255],
        'keterangan' => ['required' => false, 'control' => 'textarea'],
    ];

    public static function options()
    {
        $orderColumn = "bk." . static::OPTION_ORDER;

        return DB::table('kerjasama.bentuk_kegiatan', 'bk')
            ->whereNull('bk.waktu_dihapus')
            ->leftJoin('kerjasama.jenis_kegiatan as jk', 'bk.id_jenis_kegiatan', '=', 'jk.id')
            ->orderByRaw($orderColumn)
            ->selectRaw("bk.id, CONCAT_WS(' - ', jk.nama_jenis_kegiatan, bk.nama_bentuk_kegiatan) as nama_bentuk_kegiatan")
            ->get()
            ->pluck('nama_bentuk_kegiatan', 'id')
            ->toArray();
    }

    public function sasaran(): HasMany
    {
        return $this->hasMany(MappingSasaranBentukKegiatan::class, 'id_bentuk_kegiatan');
    }

    public static function optionsWithoutJenisKegiatan(): array
    {
        $orderColumn = "bk." . static::OPTION_ORDER;

        return DB::table('kerjasama.bentuk_kegiatan', 'bk')
            ->whereNull('bk.waktu_dihapus')
            ->orderByRaw($orderColumn)
            ->selectRaw("bk.id, bk.nama_bentuk_kegiatan as nama_bentuk_kegiatan")
            ->get()
            ->pluck('nama_bentuk_kegiatan', 'id')
            ->toArray();
    }

    public function jenisKegiatan(): BelongsTo
    {
        return $this->belongsTo(JenisKegiatan::class, 'id_jenis_kegiatan');
    }
}
