<?php

namespace Modules\Litabmas\Enums;

enum JenisPendanaanEnum: string
{
    const CODE_PENELITIAN = 'penelitian';
    const CODE_PENGABDIAN = 'pengabdian_masyarakat';

    /**
     * Default options.
     * Simple array key-value pair.
     *
     * @var array<string>
     */
    const CODES = [
        self::CODE_PENELITIAN => 'Penelitian',
        self::CODE_PENGABDIAN => 'Pengabdian Masyarakat',
    ];
}
