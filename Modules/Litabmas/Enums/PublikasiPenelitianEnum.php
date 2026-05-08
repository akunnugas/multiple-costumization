<?php

namespace Modules\Litabmas\Enums;

enum PublikasiPenelitianEnum: string
{
    const JENIS_ARTIKEL = 'artikel';
    const JENIS_BUKU = 'buku';

    /**
     * Default options.
     * Simple array key-value pair.
     *
     * @var array<string>
     */
    const JENIS_OPTIONS = [
        self::JENIS_ARTIKEL => 'Artikel',
        self::JENIS_BUKU => 'Buku',
    ];
}
