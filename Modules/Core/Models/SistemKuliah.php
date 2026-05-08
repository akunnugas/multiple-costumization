<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Cache\SistemKuliahCache;
use Modules\Core\Models\Traits\ClearCache;

class SistemKuliah extends IndonesianModel
{
    use ClearCache, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.sistem_kuliah';

    protected $fillable = ['nama_sistem', 'deskripsi_sistem'];

    /**
     * Constant order for default options in ModelTrait.
     */
    const OPTION_ORDER = 'nama_sistem';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_sistem' => ['required' => true, 'unique' => true, 'maxlength' => 50],
        'deskripsi_sistem' => ['maxlength' => 255],
    ];

    /**
     * Menampilkan opsi untuk kebutuhan filter.
     * @return array
     */
    public static function options()
    {
        return SistemKuliahCache::options();
    }

    private static function clearCache($model)
    {
        SistemKuliahCache::destroy();
    }
}
