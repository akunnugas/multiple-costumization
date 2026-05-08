<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Extensions\Models\Traits\ClearCache;
use Modules\Core\Models\Cache\JenjangPendidikanCache;

class JenjangPendidikan extends IndonesianModel
{
    use ClearCache, SoftDeletes;

    const OPTION_ORDER = 'kode_jenjang asc';
    const OPTION_COLUMN = 'nama_jenjang';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.jenjang_pendidikan';

    protected $fillable = [
        'nama_jenjang',
        'nama_jenjang_en',
        'kode_jenjang',
        'kode_dikti',
        'apakah_akademik',
        'apakah_pt',
        'apakah_pasca',
        'kode_emis',
        'kode_emis_pasca',
        'kode_emis_dosen',
        'urutan',
        'ref_key_siakad',
        'apakah_data_default'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_jenjang' => ['required' => true, 'maxlength' => 255],
        'nama_jenjang_en' => ['required' => false, 'maxlength' => 255],
        'kode_jenjang' => ['required' => true, 'maxlength' => 10, 'unique' => true],
        'kode_dikti' => ['required' => false, 'type' => 'integer'],
        'apakah_akademik' => ['type' => 'boolean'],
        'apakah_pt' => ['type' => 'boolean'],
        'apakah_pasca' => ['type' => 'boolean'],
        'kode_emis' => ['required' => false, 'maxlength' => 255],
        'kode_emis_pasca' => ['required' => false, 'maxlength' => 255],
        'kode_emis_dosen' => ['required' => false, 'maxlength' => 255],
        'urutan' => ['required' => false, 'type' => 'numeric']
    ];

    /**
     * Menampilkan opsi untuk kebutuhan filter.
     *
     * @param bool $isUniv
     * @return array
     */
    public static function options(bool $isUniv = true)
    {
        return JenjangPendidikanCache::options($isUniv);
    }

    /**
     * Clear any related cache
     *
     * @param mixed $model
     */
    private static function clearCache($model)
    {
        JenjangPendidikanCache::destroy();
    }
}
