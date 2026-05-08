<?php

namespace Modules\DMS\Helpers;

use Carbon\Carbon;

class Format {
    public static function formatDateToRelativeHuman(Carbon $date) {
        $daysDiff = $date->diffInDays(Carbon::now());

        switch (true) {
            case $daysDiff < 1:
                return 'Hari ini, ' . $date->format('H:i') . ' WIB';

            case $daysDiff < 2:
                return 'Kemarin, ' . $date->format('H:i') . ' WIB';

            default:
                return $date->format('d M Y, H:i') . ' WIB';
        }
    }
}
