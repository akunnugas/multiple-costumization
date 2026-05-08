<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class SeleksiKomponen extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.seleksi_komponen';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'kode_komponen', 'nama_komponen'
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'kode_komponen' => ['required' => true, 'unique' => true, 'maxlength' => 10], // Kode Komposisi Seleksi
        'nama_komponen' => ['required' => true, 'maxlength' => 100], // Nama Komposisi Seleksi
    ];
}
