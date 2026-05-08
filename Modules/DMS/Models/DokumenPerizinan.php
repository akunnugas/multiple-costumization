<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Extensions\Models\IndonesianModel;

class DokumenPerizinan extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.dokumen_perizinan';

    const COLLABORATOR = 'C';
    const ORGANIZATION = 'O';

    protected $fillable = [
        'id_dokumen',
        'nama_perizinan',
        'jenis_perizinan'
    ];

    /**
     * Attribut untuk visibility
     */
    protected function typeStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => match ((int) $this->visibilitas) {
                self::COLLABORATOR => 'Collaborator',
                self::ORGANIZATION => 'Organisasi',
                default => 'none',
            }
        );
    }

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'id_dokumen' => ['required' => true, 'options' => Dokumen::class],
        'nama_perizinan' => ['required' => true, 'maxlength' => 100],
        'jenis_perizinan' => ['required' => true, 'maxlength' => 1]
    ];
}
