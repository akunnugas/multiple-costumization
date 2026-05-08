<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class JabatanAkademik extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const TENAGA_KEPENDIDIKAN = 0;
    const DOSEN_AKADEMIK = 1;
    const DOSEN_PRAKTISI_INDUSTRI = 2;
    const TYPES = [
        self::TENAGA_KEPENDIDIKAN => 'Tenaga Kependidikan',
        self::DOSEN_AKADEMIK => 'Dosen',
        self::DOSEN_PRAKTISI_INDUSTRI => 'Praktisi/Industri',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.jabatan_akademik';

    protected $fillable = [
        'kode_jabatan_akademik',
        'nama_jabatan_akademik',
        'jenis_jabatan_akademik',
        'kode_emis',
        'ref_key_siakad'
    ];

    const OPTION_ORDER = 'nama_jabatan_akademik';
    const OPTION_COLUMN = 'nama_jabatan_akademik';

    /**
     * Attribut untuk visibility
     */
    protected function academicTypeName(): Attribute
    {
        return Attribute::make(
            get: fn () => match ((int) $this->jenis_jabatan_akademik) {
                self::DOSEN_AKADEMIK => 'Dosen',
                self::DOSEN_PRAKTISI_INDUSTRI => 'Praktisi/Industri',
                self::TENAGA_KEPENDIDIKAN => 'Tenaga Kependidikan',
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
        'kode_jabatan_akademik' => ['required' => true, 'maxlength' => 5, 'unique' => true],
        'nama_jabatan_akademik' => ['required' => true, 'maxlength' => 255],
        'jenis_jabatan_akademik' => [
            'required' => true, 'type' => 'integer',
            'options' => self::TYPES
        ],
        'kode_emis' => ['required' => false, 'maxlength' => 5],
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\JabatanAkademikFactory::new();
    }
}
