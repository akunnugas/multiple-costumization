<?php

namespace Modules\Core\Helpers;

use Akaunting\Money\Currency;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use NumberFormatter;

class Format
{
    /**
     * Format angka.
     *
     * @param mixed $number
     * @param int $decimals
     * @return string
     */
    public static function number($number, $decimals = 0)
    {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Menghapus trailing/suffix angka nol.
     *
     * @param $number
     * @param int $decimals
     * @return int|string
     */
    public static function removeTrailingZeroes($number, int $decimals = 2)
    {
        // Cek apakah angka adalah integer setelah dikonversi ke float
        if (floor($number) == $number) {
            // Jika ya, kembalikan sebagai integer
            return (int)$number;
        } else {
            // Jika tidak, kembalikan sebagai float dengan dua desimal
            return number_format($number, $decimals);
        }
    }

    /**
     * Format timestamp.
     *
     * @param mixed $time
     * @return string
     */
    public static function timestamp($time = null)
    {
        $format = 'l, j F Y, H:i';

        if (empty($time)) {
            $time = Carbon::now();
        } else {
            $time = Carbon::parse($time);
        }

        return $time->setTimezone(config('app.timezone'))->locale('id')->translatedFormat($format);
    }

    /**
     * Format byte to human
     *
     * @param int $size
     * @param int $precision
     */
    public static function formatBytes($size, $precision = 0)
    {
        $base = log($size, 1024);
        $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    /**
     * Format bytes to kilobytes.
     *
     * @param int $bytes
     * @return int
     */
    public static function formatKbToBytes($kiloBytes)
    {
        $bytes = $kiloBytes * 1024;

        return $bytes;
    }

    /**
     * Format angka menjadi singkatan.
     * @param int $number
     * @return string
     */
    public static function numberAbbv($number)
    {
        $abbreviations = [
            12 => 'T',
            9 => 'm',
            6 => 'jt',
            3 => 'k',
        ];

        foreach ($abbreviations as $exponent => $suffix) {
            $divisor = pow(10, $exponent);
            if ($number >= $divisor) {
                // tampilkan tanpa desimal apabila habis dibagi
                if ($number % $divisor === 0) {
                    return number_format($number / $divisor) . $suffix;
                }

                if (($number % $divisor) % 100 === 0) {
                    return number_format($number / $divisor, 1) . $suffix;
                }

                return number_format($number / $divisor, 2) . $suffix;
            }
        }

        return (string) $number;
    }

    /**
     * Format currency abbreviation dengan prefix mata uang.
     * @param  Currency  $currency
     * @param  int  $amount
     *
     * @return string
     */
    public static function formatCurrencyAbbv(Currency $currency, int $amount)
    {
        $shorthand = self::numberAbbv($amount);
        $prefix = $currency->getPrefix();

        return "$prefix$shorthand";
    }

    /**
     * Value dari choices.js.
     * @param $value
     *
     * @return mixed
     */
    public static function choicesValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        if (!isset($value)) {
            return null;
        }

        return $value;
    }

    /**
     * Value berdasarkan type.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public static function valueByType(mixed $value, string $type)
    {
        if ($type === 'timestamp') {
            $value = !empty($value) ? (Format::timestamp($value)) : null;
        } elseif ($type === 'date') {
            $value = !empty($value) ? (Date::formatDate($value)) : null;
        } elseif ($type === 'time') {
            $value = !empty($value) ? (Date::formatDate($value, 'H:i')) : null;
        } elseif ($type === 'boolean') {
            $value = !empty($value) ? 'Ya' : 'Tidak';
        }

        return $value;
    }

    /**
     * Value berdasarkan control.
     *
     * @param mixed $value
     * @param string $control
     * @param mixed $options
     * @return mixed
     */
    public static function valueByControl(mixed $value, string $control, mixed $options = null): mixed
    {
        if ($control === 'wysiwyg') {
            $value = strip_tags($value, '<p><a><b><i><u><strong><em><br><ul><ol><li><h1><h2><h3><h4><h5><h6><img><table><tr><td><th><tbody><thead><tfoot><caption><div><span><hr><pre><code><blockquote><cite><small><sub><sup><del><ins><mark><abbr><acronym><address><dfn><kbd><samp><var><ul><ol><li><dl><dt><dd>');
        } elseif ($control === 'radio') {
            $value = $options[$value] ?? null;
        } elseif ($control === 'select-multiple') {
            $result = [];
            if (!empty($value) && is_array($value)) {
                foreach ($value as $val) {
                    $result[] = $options[$val] ?? null;
                }

                $value = implode(', ', $result);
            }
        } elseif ($control === 'autocomplete') {
            if (!is_array($options) && method_exists($options, 'optionValue')) {
                $value = $options::optionValue($value);
            } else {
                $value = $options[$value] ?? null;
            }
        }

        return $value;
    }

    /**
     * Build array error message to string.
     *
     * @param array $errors // isinya array dengan key - value
     * @param array $options // isinya array dengan key - value
     * @return string
     */
    public static function buildArrayErrorTextByOptions(array $errors, array $options): string
    {
        $mappedErrors = array_map(fn($error) => ($options[$error] ?? $error), $errors);

        // handle jika $errors cuma satu
        if (count($mappedErrors) == 1) {
            return reset($mappedErrors);
        }

        // lebih dari satu
        $last = array_pop($mappedErrors);
        return implode(', ', $mappedErrors) . ' & ' . $last;
    }

    /**
     * Build array error message to string.
     *
     * @param array $errors // isinya array dengan key - value
     * @return string
     */
    public static function buildArrayErrorText(array $errors): string
    {
        // handle jika $errors cuma satu
        if (count($errors) == 1) {
            return reset($errors);
        }

        // lebih dari satu
        $last = array_pop($errors);
        return implode(', ', $errors) . ' & ' . $last;
    }

    /**
     * Remove unit ketika tidak memiliki child.
     * Ex: fakultas yg prodinya kosong akan dihapus.
     *
     * @param array $daftarUnitKerja
     * @return array
     */
    public static function removeUnitHasEmptyChild(array $daftarUnitKerja)
    {
        // cek apakah isi dalemnya object atau array
        $isObject = false;
        $first = Arr::first($daftarUnitKerja);
        if (is_object($first)) {
            $isObject = true;
            $daftarUnitKerja = json_decode(json_encode($daftarUnitKerja), true); // konversi objek ke array
        }

        // looping ulang utk cek jika fakultas tidak ada prodi pasca, maka hapus fakultas
        foreach ($daftarUnitKerja as $key => $item) {
            if ($item['jenis_unit'] == 'F') {
                $idFakultas = $item['id'];
                $isExistProdiPasca = false;
                foreach ($daftarUnitKerja as $itemProdi) {
                    if ($itemProdi['jenis_unit'] == 'P' && $itemProdi['id_parent'] == $idFakultas) {
                        $isExistProdiPasca = true;
                        break;
                    }
                }

                if (!$isExistProdiPasca) {
                    unset($daftarUnitKerja[$key]);
                }
            }
        }

        // looping ulang utk cek jika universitas tidak ada fakultas pasca, maka hapus universitas
        foreach ($daftarUnitKerja as $key => $item) {
            if ($item['jenis_unit'] == 'U') {
                $idUniversitas = $item['id'];
                $isExistFakultasPasca = false;
                foreach ($daftarUnitKerja as $itemFakultas) {
                    if ($itemFakultas['jenis_unit'] == 'F' && $itemFakultas['id_parent'] == $idUniversitas) {
                        $isExistFakultasPasca = true;
                        break;
                    }
                }

                if (!$isExistFakultasPasca) {
                    unset($daftarUnitKerja[$key]);
                }
            }
            break;
        }

        // Jika data awal adalah objek, kembalikan dalam bentuk objek
        if ($isObject) {
            return json_decode(json_encode($daftarUnitKerja)); // kembalikan ke objek
        }

        return $daftarUnitKerja;
    }

    /**
     * Format the number to a currency format.
     *
     * @param  float|int  $number
     * @param  string  $currency
     * @param  string  $locale
     * @return false|string
     */
    public static function toCurrency($number, $currency = 'USD', $locale = 'en')
    {

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($number, $currency);
    }
}
