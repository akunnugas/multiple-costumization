<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\PMB\Models\Pendaftar;
use Modules\PMB\Models\PeriodePendaftaran;

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
        'id_pendaftar',
        'id_periode_pendaftaran',
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
        'id_pendaftar' => ['options' => Pendaftar::class], // Pendaftar
        'id_periode_pendaftaran' => ['options' => PeriodePendaftaran::class], // Periode Pendaftaran
    ];

    /**
     * Jenis Penerima (source: siakadCloud lama m_penerimabroadcast)
     */
    const TYPE_MAHASISWA = 'M';
    const TYPE_DOSEN = 'D';
    const TYPE_PENDAFTAR = 'P';
    const TYPE_PERIODEDAFTAR = 'PD';
    const TYPE_KELUARGA = 'K';
    const TYPE_ALUMNI = 'A';
    const TYPES = [
        self::TYPE_MAHASISWA => 'Mahasiswa',
        self::TYPE_DOSEN => 'Dosen',
        self::TYPE_PENDAFTAR => 'Pendaftar',
        self::TYPE_PERIODEDAFTAR => 'Periode Pendaftaran',
        self::TYPE_KELUARGA => 'Keluarga',
        self::TYPE_ALUMNI => 'Alumni',
    ];

    /**
     * Jenis Penerima berdasarkan module nya.
     */
    const TYPE_OF_MODULE_PMB = [
        self::TYPE_PENDAFTAR => 'Pendaftar',
        self::TYPE_PERIODEDAFTAR => 'Periode Pendaftaran',
    ];
    const TYPE_OF_MODULE_AKADEMIK = [
        self::TYPE_MAHASISWA,
        self::TYPE_DOSEN,
        self::TYPE_KELUARGA,
    ];

    public static function getSearchRecipientOptions($type)
    {
        if ($type == self::TYPE_PENDAFTAR) {
            $sql = "select r.id as key, concat(r.kode_pendaftar, ' - ', p.nama) as value
                from pmb.pendaftar r
                join core.biodata p on r.person_id = p.id
                where 1=1";
        }

        if ($type == self::TYPE_PERIODEDAFTAR) {
            $sql = "select id as key, nama_periode as value
                from pmb.periode_pendaftaran
                where 1=1";
        }

        $result = DB::select($sql);

        // make to array option
        return array_column($result, 'value', 'key');
    }
}
