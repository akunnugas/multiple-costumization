<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class PerguruanTinggi extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.perguruan_tinggi';

    protected $fillable = [
        'nama_pt', 'kode_pt', 'alamat_pt', 'telepon_pt', 'ref_key_siakad'
    ];

    /**
     * Constant order for options.
     * @var string
     */
    const OPTION_ORDER = 'nama_pt asc';
    const OPTION_COLUMN = 'nama_pt';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_pt' => ['required' => true, 'maxlength' => 20],
        'nama_pt' => ['required' => true, 'maxlength' => 100],
        'alamat_pt' => ['maxlength' => 255],
        'telepon_pt' => ['maxlength' => 20],
        'ref_key_siakad' => ['maxlength' => 255],
    ];

    //search autocomplete
    public static function searchOption($query, $limit = 5)
    {
        $return = self::where('nama_pt', 'ilike', '%' . $query . '%')
            ->orderBy('nama_pt')
            ->limit($limit)
            ->get(['id', 'nama_pt']);
        //mapping data
        $return = $return->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->nama_pt
            ];
        });
            
        return $return;
    }
}
