<?php

namespace Modules\Kerjasama\Enums;

enum JenisMitra: string 
{

    case PERGURUAN_TINGGI = 'PT';
    case INSTANSI = 'INS';

    public static function getOptions()
    {
        return [
            self::PERGURUAN_TINGGI->value => 'Perguruan Tinggi',
            self::INSTANSI->value => 'Non Perguruan Tinggi'
        ];
    }

}