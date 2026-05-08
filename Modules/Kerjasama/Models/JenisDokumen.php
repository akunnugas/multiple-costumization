<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JenisDokumen extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'jenis_dokumen asc';
    const OPTION_COLUMN = 'jenis_dokumen';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.jenis_dokumen';

    protected $fillable = [
        'jenis_dokumen',
        // 'keterangan',
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
        'jenis_dokumen' => ['required' => true, 'maxlength' => 255, 'unique_ci' => true],
        // 'keterangan' => ['required' => false],
    ];

    public function kerjasama(): HasMany
    {
        return $this->hasMany(Kerjasama::class);
    }



    // protected static function newFactory()
    // {
    //     return \Modules\Kerjasama\Database\factories\JenisDokumenFactory::new();
    // }
}
