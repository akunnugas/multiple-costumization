<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JalurPendaftaran extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.jalur_pendaftaran';

    protected $fillable = ['nama_jalur', 'keterangan'];

    /**
     * Constant order for default options in ModelTrait.
     */
    const OPTION_ORDER = 'nama_jalur';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'nama_jalur' => ['required' => true, 'maxlength' => 100, 'unique' => true], // Nama Jalur Pendaftaran
        'keterangan' => ['maxlength' => 255], // Deskripsi Jalur Pendaftaran
        // tidak konek ke registration_type karena sudah diwakili oleh registration_period (source excel)
    ];
}
