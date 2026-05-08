<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;

class BroadcastPenerima extends IndonesianModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.broadcast_penerima';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_broadcast',
        'apakah_email_terkirim',
        'apakah_whatsapp_terkirim',
        'apakah_sms_terkirim',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_broadcast' => ['required' => true, 'options' => Broadcast::class], // Broadcast
        'apakah_email_terkirim' => ['required' => true, 'type' => 'boolean'], // Email Terkirim
        'apakah_whatsapp_terkirim' => ['required' => true, 'type' => 'boolean'], // Whatsapp Terkirim
        'apakah_sms_terkirim' => ['required' => true, 'type' => 'boolean'], // SMS Terkirim
    ];

    /**
     * Jenis Penerima (source: siakadCloud lama m_penerimabroadcast)
     */
    const TYPE_MAHASISWA = 'M';
    const TYPE_DOSEN = 'D';
    const TYPE_KELUARGA = 'K';
    const TYPE_ALUMNI = 'A';
    const TYPES = [
        self::TYPE_MAHASISWA => 'Mahasiswa',
        self::TYPE_DOSEN => 'Dosen',
        self::TYPE_KELUARGA => 'Keluarga',
        self::TYPE_ALUMNI => 'Alumni',
    ];

    /**
     * Jenis Penerima berdasarkan module nya.
     */
    const TYPE_OF_MODULE_AKADEMIK = [
        self::TYPE_MAHASISWA,
        self::TYPE_DOSEN,
        self::TYPE_KELUARGA,
    ];

    public static function getSearchRecipientOptions($type)
    {
        return [];
    }
}
