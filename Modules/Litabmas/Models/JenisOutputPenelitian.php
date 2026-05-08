<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Core\Models\Traits\ClearCache;
use Modules\Litabmas\Database\factories\JenisOutputPenelitianFactory;
use Modules\Litabmas\Models\Cache\JenisOutputPenelitianCache;

class JenisOutputPenelitian extends IndonesianModel
{
    use HasFactory, SoftDeletes, ClearCache;

    const OPTION_COLUMN = 'nama_output';
    const OPTION_ORDER = 'nama_output asc';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.jenis_output_penelitian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_output',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'nama_output' => ['required' => true, 'maxlength' => 100, 'unique' => true],                            // Nama Output
    ];

    protected static function newFactory()
    {
        return JenisOutputPenelitianFactory::new();
    }

    /**
     * Display options.
     * @return array
     */
    public static function options()
    {
        return JenisOutputPenelitianCache::options();
    }

    /**
     * Get list data output dari database (cache).
     *
     * @return Collection
     */
    public function getListCache()
    {
        return JenisOutputPenelitianCache::get();
    }

    /**
     * When model is booted, do something.
     *
     * @return void
     */
    protected static function booted()
    {
        parent::booted();

        static::saved(function ($model) {
            self::clearCache($model);
            static::clearCacheCustom(JenisOutputPenelitianCache::KEY_OPTIONS);
        });
        static::updated(function () {
            static::clearCacheCustom(JenisOutputPenelitianCache::KEY_OPTIONS);
        });
        static::deleted(function () {
            static::clearCacheCustom(JenisOutputPenelitianCache::KEY_OPTIONS);
        });
    }

    /**
     * Clear/forgot cache.
     *
     * @param $model
     * @return void
     */
    private static function clearCache($model)
    {
        JenisOutputPenelitianCache::destroy();
    }

    /**
     * Clear/forgot custom cache.
     *
     * @param string $key
     * @return void
     */
    private static function clearCacheCustom($key)
    {
        JenisOutputPenelitianCache::destroyCustomKey($key);
    }
}
