<?php

namespace Modules\Kerjasama\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class StatusKerjasama extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'status_kerjasama asc';
    const OPTION_COLUMN = 'status_kerjasama';

    const DRAFT = 'Draft';
    const AKTIF = 'Aktif';
    const KADALUWARSA = 'Kedaluwarsa';
    const SELESAI = 'Selesai';
    const TIDAK_AKTIF = 'Tidak Aktif';
    const PERPANJANG = 'Perpanjangan';

    const PERPANJANGANDASHBOARD= 'Dalam Perpanjangan'; 

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kerjasama.status_kerjasama';

    protected $fillable = [
        'status_kerjasama',
        'keterangan',
    ];

    protected $guarded = [
        'id',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [
        'status_kerjasama' => ['required' => true, 'maxlength' => 255],
        'keterangan' => ['required' => false],
    ];

    public static function getBadgeType(string $status) {
        return match ($status) {
            self::AKTIF => 'success',
            self::KADALUWARSA => 'warning',
            self::TIDAK_AKTIF => 'danger',
            self::PERPANJANG => 'primary',
            default => 'dark'
        };
    }

    public static function getIcon(string $status) {
        return match ($status) {
            self::AKTIF => 'check-square-broken',
            self::KADALUWARSA => 'alert-triangle',
            self::TIDAK_AKTIF => 'x-circle',
            self::PERPANJANG => 'clock-refresh',
            default => null
        };
    }
}
