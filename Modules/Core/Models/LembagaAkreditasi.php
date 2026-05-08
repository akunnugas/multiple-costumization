<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class LembagaAkreditasi extends IndonesianModel
{
    use SoftDeletes;

    const OPTION_ORDER = 'kode_lembaga asc';
    const OPTION_COLUMN = 'nama_lembaga';

    const BANPT = 'BANPT';
    const LAMEMBA = 'LAMEMBA';
    const LAMPT = 'LAMPT';
    const LAMPTPD = 'LAMPTPD';
    const LAMPTSI = 'LAMPTSI';
    const LAMPTTK = 'LAMPTTK';
    const LAMSAMA = 'LAMSAMA';
    const LAMDEPILAR = 'LAMDEPILAR';
    const LAMSPAK = 'LAMSPAK';

    const LEMBAGA_AKREDITASI = [
        self::BANPT => 'BAN-PT',
        self::LAMEMBA => 'LAM EMBA',
        self::LAMPT => 'LAM-PTKes',
        self::LAMPTPD => 'LAM Kependidikan',
        self::LAMPTSI => 'LAM Infokom',
        self::LAMPTTK => 'LAM Teknik',
        self::LAMSAMA => 'LAM Sama',
        self::LAMDEPILAR => 'LAM Depilar',
        self::LAMSPAK => 'LAM Spak',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.lembaga_akreditasi';

    protected $fillable = [
        'kode_lembaga',
        'nama_lembaga',
        'nama_singkat_lembaga'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_lembaga' => ['required' => true, 'maxlength' => 10],
        'nama_lembaga' => ['required' => true, 'maxlength' => 255],
        'nama_singkat_lembaga' => ['required' => false, 'maxlength' => 25],
    ];

    /**
     * Get List accreditation agency short_name|id
     *
     * @return array
     */
    public static function getListShortName()
    {
        return self::pluck('nama_singkat_lembaga', 'id')->toArray();
    }
}
