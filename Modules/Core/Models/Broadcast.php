<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;

class Broadcast extends IndonesianModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core.broadcast';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'judul',
        'isi',
        'apakah_email',
        'apakah_sms',
        'apakah_whatsapp',
        'jumlah_penerima',
        'jumlah_email_terkirim',
        'jumlah_whatsapp_terkirim',
        'jumlah_sms_terkirim',
        'apakah_terkirim',
        'jenis_modul',
    ];

    const MODULE_TYPE_SIAKAD = 'S';
    const MODULE_TYPE_PMB = 'P';
    const MODULE_TYPE_ALUMNI = 'A';
    const MODULE_TYPES = [
        self::MODULE_TYPE_SIAKAD => self::MODULE_TYPE_SIAKAD,
        self::MODULE_TYPE_PMB => self::MODULE_TYPE_PMB,
        self::MODULE_TYPE_ALUMNI => self::MODULE_TYPE_ALUMNI,
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'judul' => ['required' => true, 'maxlength' => 255], // Judul Broadcast
        'isi' => [], // Konten Broadcast
        'apakah_email' => ['type' => 'boolean'], // Kirim Melalui Email
        'apakah_sms' => ['type' => 'boolean'], // Kirim Melalui SMS
        'apakah_whatsapp' => ['type' => 'boolean'], // Kirim Melalui Whatsapp
        'jumlah_penerima' => ['type' => 'integer'], // Jumlah Penerima
        'jumlah_email_terkirim' => ['type' => 'integer'], // Jumlah Email Terkirim
        'jumlah_whatsapp_terkirim' => ['type' => 'integer'], // Jumlah Whatsapp Terkirim
        'jumlah_sms_terkirim' => ['type' => 'integer'], // Jumlah SMS Terkirim
        'apakah_terkirim' => ['type' => 'boolean'], // Sudah Terkirim
        'jenis_modul' => ['maxlength' => 1, 'options' => self::MODULE_TYPES], // Jenis Module
    ];
}
