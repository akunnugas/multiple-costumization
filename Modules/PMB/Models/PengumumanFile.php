<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class PengumumanFile extends IndonesianModel
{
    use HasFactory, SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.pengumuman_file';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_file',
        'id_pengumuman',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_file' => ['required' => true, 'options' => Dokumen::class], // Dokumen
        'id_pengumuman' => ['required' => true, 'options' => Pengumuman::class], // Pengumuman
    ];

    const ACCEPTED_TYPES = [
        'pdf', 'png', 'jpg', 'doc', 'docx'
    ];
}
