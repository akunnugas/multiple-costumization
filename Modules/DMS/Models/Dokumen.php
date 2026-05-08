<?php

namespace Modules\DMS\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Extensions\Models\IndonesianModel;

class Dokumen extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    const OPTION_ORDER = 'nama_dokumen asc';

    const VISIBILITY_PRIVATE = 1;
    const VISIBILITY_PUBLIC = 2;

    const TYPE_DOCUMENT = ['pdf', 'doc', 'docx'];
    const TYPE_IMAGE = ['png', 'jpg', 'jpeg'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dms.dokumen';

    protected $fillable = [
        'id_folder',
        'nama_dokumen',
        'slug',
        'ukuran',
        'kode_modul',
        'catatan',
        'visibilitas',
        'alamat_versi_terbaru',
        'extension_versi_terbaru',
        'versi_terbaru',
    ];

    /**
     * Attribut untuk visibility
     */
    protected function visibilityStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => match ((int) $this->visibilitas) {
                self::VISIBILITY_PUBLIC => 'Public',
                self::VISIBILITY_PRIVATE => 'Private',
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
        'id_folder' => ['required' => true, 'options' => Folder::class],
        'nama_dokumen' => ['required' => true, 'maxlength' => 255],
        'slug' => ['required' => true],
        'ukuran' => ['required' => true],
        'kode_modul' => ['required' => true, 'maxlength' => 100],
        'catatan' => ['required' => false, 'maxlength' => 255],
        'visibilitas' => ['required' => true],
        'alamat_versi_terbaru' => ['required' => true, 'maxlength' => 255],
        'extension_versi_terbaru' => ['required' => true, 'maxlength' => 10],
        'versi_terbaru' => ['required' => true, 'type' => 'integer'],
    ];

    public function versions()
    {
        return $this->hasMany(DokumenVersi::class, 'id_dokumen', 'id');
    }

    public function permissions()
    {
        return $this->hasMany(DokumenPerizinan::class, 'id_dokumen', 'id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'id_folder', 'id');
    }

    public function lastVersionSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->versions()->orderBy('versi', 'desc')->first()?->ukuran ?? 0
        );
    }

    public function lastVersionTemporaryUrl(int $expirationMinute = 15): string
    {
        return Storage::temporaryUrl(
            path: $this->alamat_versi_terbaru,
            expiration: now()->addMinutes($expirationMinute)
        );
    }

    public static function generateLastVersionTemporaryUrl($path, int $expirationMinute = 15): string
    {
        return Storage::temporaryUrl(
            path: $path,
            expiration: now()->addMinutes($expirationMinute)
        );
    }

    protected static function newFactory()
    {
        return \Modules\DMS\Database\factories\DokumenFactory::new();
    }
}
