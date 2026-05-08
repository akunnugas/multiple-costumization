<?php

namespace Modules\PMB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\DMS\Models\Dokumen;

class Pengumuman extends IndonesianModel
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pmb.pengumuman';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul_pengumuman', 'link_pengumuman', 'isi_pengumuman', 'jenis_pengumuman', 'apakah_aktif', 'id_file_gambar',
    ];

    const TYPE_ANNOUNCEMENT = 'U';
    const TYPE_INFORMATION = 'I';
    const TYPE_BROCHURE = 'B';
    const TYPES = [
        self::TYPE_ANNOUNCEMENT => 'Pengumuman',
        self::TYPE_INFORMATION => 'Informasi',
        self::TYPE_BROCHURE => 'Brosur',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'judul_pengumuman' => ['required' => true, 'maxlength' => 255], // Judul
        'link_pengumuman' => ['required' => true, 'unique' => true, 'maxlength' => 255], // Link
        'isi_pengumuman' => ['required' => true], // Deskripsi
        'jenis_pengumuman' => ['required' => true, 'maxlength' => 1, 'options' => self::TYPES], // Jenis: U = Pengumuman, I = Informasi, B = Brosur
        'apakah_aktif' => ['type' => 'boolean'], // Aktif
        'id_file_gambar' => ['file_type' => Dokumen::TYPE_IMAGE, 'max_size' => 1024 * 5], // Gambar
    ];

    public function file(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PengumumanFile::class);
    }
}
