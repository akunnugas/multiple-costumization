<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Carbon;

class Date
{
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Mendapatkan opsi tahun untuk dropdown berdasarkan tahun.
     *
     * @param int|null $startYear   Tahun awal (opsional, default: 10 tahun sebelum tahun saat ini)
     * @param int $yearRange        Rentang tahun (opsional, default: 10 tahun)
     * @param bool $descending      Urutan menurun atau tidak (default: true)
     * @return array                Array opsi tahun (array asosiatif dengan kunci dan nilai yang sama)
     * @old: getListTahun in spmb/models/m_periodedaftar.php
     */
    public static function getYearOptions(int $startYear = null, int $yearRange = 10, bool $descending = false)
    {
        // Mendapatkan tahun saat ini menggunakan fungsi now() dari Laravel
        $currentYear = now()->year;

        // Jika $startYear kosong, atur nilainya ke 10 tahun sebelum tahun saat ini
        if (is_null($startYear)) {
            $startYear = $currentYear - $yearRange;
        }

        // Set rentang tahun berdasarkan kondisi apakah menurun (descending) atau tidak
        $yearRange = $descending
            ? range($startYear, $currentYear + 1)
            : range($currentYear + 1, $startYear, -1);

        // Return array asosiatif dengan key dan value yang sama
        return array_combine($yearRange, $yearRange);
    }

    /**
     * Memeriksa apakah waktu/tanggal tertentu berada dalam rentang yang ditentukan.
     *
     * @param string $start Waktu awal rentang (format: Y-m-d H:i:s).
     * @param string $end Waktu akhir rentang (format: Y-m-d H:i:s).
     * @param string|null $date Tanggal yang akan diperiksa (format: Y-m-d H:i:s). Jika null, menggunakan waktu saat ini.
     * @return bool True jika tanggal berada dalam rentang, false sebaliknya.
     * @old: cekBuka() in spmb/models/m_periodedaftar.php
     */
    public static function isDateInRange(string $start, string $end, string $date = null)
    {
        if (empty($date)) {
            $date = date(self::DATE_TIME_FORMAT);
        }

        // set format waktu
        $start = Carbon::parse($start)->format(self::DATE_TIME_FORMAT);
        $end = Carbon::parse($end)->format(self::DATE_TIME_FORMAT);
        $date = Carbon::parse($date)->format(self::DATE_TIME_FORMAT);

        // konversi ke unix timestamp
        $start = strtotime($start);
        $end = strtotime($end);
        $date = strtotime($date);

        // true jika $date berada diantara $start dan $end
        return $date >= $start && $date <= $end;
    }

    /**
     * Iso format rentang tanggal menjadi format yang lebih mudah dibaca.
     *
     * @param string $startDate Tanggal awal rentang (format: Y-m-d).
     * @param string $endDate Tanggal akhir rentang (format: Y-m-d).
     * @param string $separator Pemisah antara tanggal awal dan akhir (default: '-').
     * @param string $isoFormatDay Format hari dalam ISO (default: 'D').
     * @param string $isoFormatMonth Format bulan dalam ISO (default: 'MMM').
     * @param string $isoFormatYear Format tahun dalam ISO (default: 'YYYY').
     * @return string Tanggal dalam format yang lebih mudah dibaca.
     */
    public static function formatDateRange(
        string $startDate,
        string $endDate,
        string $separator = '-',
        string $isoFormatDay = 'D',
        string $isoFormatMonth = 'MMM',
        string $isoFormatYear = 'YYYY'
    ): string
    {
        $startDate = Carbon::parse($startDate)->locale('id');
        $endDate = Carbon::parse($endDate)->locale('id');

        $isoFormat = "$isoFormatDay $isoFormatMonth $isoFormatYear";

        if ($startDate->equalTo($endDate)) {
            $formattedDate = $startDate->isoFormat($isoFormat);
        } elseif ($startDate->isSameYear($endDate)) {
            $isoFormatDayAndMonth = "$isoFormatDay $isoFormatMonth";
            $formattedDate = $startDate->isoFormat($isoFormatDayAndMonth) . " $separator " . $endDate->isoFormat($isoFormat);
        } else {
            $formattedDate = $startDate->isoFormat($isoFormat) . " $separator " . $endDate->isoFormat($isoFormat);
        }

        return $formattedDate;
    }

    /**
     * Iso format single date
     *
     * @param $date
     * @param $format
     * @return string
     */
    public static function formatDate($date, $format = 'D MMMM YYYY')
    {
        $date = Carbon::parse($date)->locale('id');

        return $date->isoFormat($format);
    }

    /**
     * Format tanggal indonesia (panjang)
     * ex: Rabu, 5 Juni 2024, 11:09 WIB
     *
     * @param $date
     * @param $date_end
     * @return array|string|string[]|null
     */
    public static function formatDateTimeLong($date, $date_end = null)
    {
        if (empty($date)) {
            return null;
        }

        $carbon = \Carbon\Carbon::parse($date)->locale('id');
        $format = static::convertTimeZone($carbon->translatedFormat('l, j F Y, H:i [O]'));

        if (!empty($date_end)) {
            $format .= ' s.d. ';
            $carbon_end = Carbon::parse($date_end)->locale('id');

            if ($carbon_end->format('Ymd') != $carbon->format('Ymd')) {
                $format .= $carbon_end->translatedFormat('l, j F Y, ');
            }

            $format .= static::convertTimeZone($carbon_end->translatedFormat('H:i [O]'));
        }

        return $format;
    }


    /**
     * Convert dari format + to timezone.
     *
     * @param $str
     * @return array|string|string[]
     */
    public static function convertTimeZone($str)
    {
        return str_replace(['[+0700]', '[+0800]', '[+0900]'], ['WIB', 'WITA', 'WIT'], $str);
    }

}
