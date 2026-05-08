<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SeleksiJenis extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi_jenis';

    protected $fillable = ['kode_jenis_seleksi','nama_jenis_seleksi'];

    /**
     * Constant order for default options in ModelTrait.
     */
    const OPTION_ORDER = 'nama_jenis_seleksi';

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'kode_jenis_seleksi' => ['required' => true, 'unique' => true, 'maxlength' => 100], // Kode Jenis Seleksi
        'nama_jenis_seleksi' => ['required' => true, 'maxlength' => 100], // Nama Jenis Seleksi
    ];

    /**
     * Menampilkan list option dari 1 hingga n berdasarkan jumlah data assessment type.
     *
     * @return array
     */
    public static function optionsAmount(): array
    {
        $count = static::count();
        if ($count === 0) {
            return [];
        }

        $range = range(1, $count);

        return array_combine($range, $range);
    }
}
