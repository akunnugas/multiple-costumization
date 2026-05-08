<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class DokumenTag extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.dokumen_tag';

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_dokumen' => ['required' => true, 'options' => Dokumen::class],
        'id_tag' => ['required' => true, 'options' => Tag::class],
    ];
}
