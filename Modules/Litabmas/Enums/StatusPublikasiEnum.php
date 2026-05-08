<?php

namespace Modules\Litabmas\Enums;

enum StatusPublikasiEnum: string
{
    const STATUS_PUBLIKASI_SELESAI = 'selesai';
    const STATUS_PUBLIKASI_DIBLOKIR = 'diblokir';
    const STATUS_PUBLIKASI_OPTIONS = [
        self::STATUS_PUBLIKASI_SELESAI => 'Selesai',
        self::STATUS_PUBLIKASI_DIBLOKIR => 'Diblokir'
    ];
}
