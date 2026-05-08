<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class Tag extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.tag';

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_tag' => ['required' => true, 'maxlength' => 100],
    ];

    protected static function newFactory()
    {
        return \Modules\DMS\Database\factories\TagFactory::new();
    }

    public function dokumenTag()
    {
        return $this->hasMany(DokumenTag::class, 'id_tag', 'id');
    }
}
