<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Wilayah extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.wilayah';

    protected $fillable = [
        'nama_wilayah',
        'id_parent',
        'kode_wilayah',
        'level_wilayah',
        'kode_dikti',
        'kode_bps',
        'kode_dagri',
        'kode_keuangan',
        'ref_key_siakad',
        'waktu_terakhir_sync'
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_wilayah';
    const OPTION_COLUMN = 'nama_wilayah';

    /**
     * Level wilayah
     */

    //temp
    const LEVEL_COUNTRY = 0;
    const LEVEL_PROVINCE = 1;
    const LEVEL_CITY = 2;
    const LEVEL_DISTRICT = 3;

    const LEVEL_NEGARA = 0;
    const LEVEL_PROVINSI = 1;
    const LEVEL_KOTA_KABUPATEN = 2;
    const LEVEL_KECAMATAN = 3;
    const LEVELS = [
        self::LEVEL_NEGARA => 'Negara',
        self::LEVEL_PROVINSI => 'Provinsi',
        self::LEVEL_KOTA_KABUPATEN => 'Kabupaten/Kota',
        self::LEVEL_KECAMATAN => 'Kecamatan',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_wilayah' => ['required' => true, 'maxlength' => 255],
        'id_parent' => ['required' => false, 'options' => Wilayah::class],
        'kode_wilayah' => ['required' => true, 'maxlength' => 255, 'unique' => true],
        'level_wilayah' => ['required' => true, 'maxlength' => 1, 'options' => self::LEVELS],
        'kode_dikti' => ['required' => false, 'maxlength' => 255],
        'kode_bps' => ['required' => false, 'maxlength' => 255],
        'kode_dagri' => ['required' => false, 'maxlength' => 255],
    ];

    /**
     * Display options by region level and parent id.
     *
     * @param int $level
     * @param int|null $parentId
     * @param string $orderBy
     * @return array
     */
    public static function optionsByLevel(int $level, int|string $parentId = null, string $orderBy = self::OPTION_ORDER)
    {
        return self::query()->where('level_wilayah', $level)
            ->when($parentId, function ($query, $parentId) {
                return $query->where('id_parent', $parentId);
            })
            ->orderByRaw($orderBy)
            ->get(['id', static::OPTION_COLUMN])
            ->pluck(static::OPTION_COLUMN, 'id')
            ->toArray();
    }

    public static function getWilayahByCode($code)
    {
        return self::query()->where('kode_wilayah', $code)->first();
    }


    /**
     * Get id parent wilayah by id and level wilayah.
     * @param int $level
     * @param int $id
     * @return int|null
     */
    public static function getIdParentWilayahById($level, $id){
        return self::query()->where('level_wilayah', $level)
            ->where('id', self::query()->where('id', $id)->value('id_parent'))->value('id');
    }
}
