<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PublicationMedia extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.publication_medias';

    protected $fillable = [
        'sister_id', // ID Sister
        'name', // Nama Media Publikasi
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'sister_id' => ['required' => false, 'maxlength' => 255],
        'name' => ['required' => true, 'maxlength' => 255],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\PublicationMediaFactory::new();
    }
}
