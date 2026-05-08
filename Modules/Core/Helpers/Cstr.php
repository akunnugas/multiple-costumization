<?php

namespace Modules\Core\Helpers;

class Cstr
{

    /**
     * create akademik year format
     * @param integer $yearPeriode
     * @param integer $decresed
     * @return string
     */
    public static function akademikYear(int $yearPeriode, int $min = 4)
    {
        $year = $yearPeriode - ($min + 1);

        return $year . '/' . ($year + 1);
    }

    /**
     * Get min max for akademik year
     *
     */
    public static function akademikYearMinMax(int $yearPeriode, int $min = 4)
    {
        $year = $yearPeriode - $min;

        return [
            $year,
            $yearPeriode - 1
        ];
    }

    public static function stripHTMLTags($string)
    {
        $string = str_replace(["\xC2\xA0", '&nbsp;'], ' ', $string);
        $string = str_replace('<br>', "\n", $string);
        return strip_tags($string);
    }

    /**
     *
     * @param map $condition
     * @param string $before
     * @return string
     */
    public static function setCondition($condition, $before = 'WHERE')
    {
        $where = array();
        foreach ($condition as $key => $val) {
            if (is_numeric($key)) {
                $where[] = $val;
            } elseif (is_string($key)) {
                $list = explode(' ', $key);
                $field = $list[0];
                unset($list[0]);
                $operator = implode(' ', $list);
                if (empty($operator))
                    $operator = '=';
                if (is_null($val)) {
                    $where[] = $field . ' ' . $operator . ' NULL';
                } else {
                    if (!empty($list[1]) == 'beetween') {
                        $where[] = $field . ' ' . $operator . ' \'' . $val[0] . '\' AND \'' . $val[1] . '\'';
                    } else if (is_numeric($val)) {
                        $where[] = $field . ' ' . $operator . ' \'' . $val . '\'';
                    } elseif (is_string($val)) {
                        $where[] = $field . ' ' . $operator . ' \'' . $val . '\'';
                    } elseif (is_array($val)) {
                        $operator = 'in';
                        $where[] = $field . ' ' . $operator . ' (\'' . implode("','", $val) . '\')';
                    }
                }
            }
        }
        if (!empty($where)) {
            return ' ' . $before . ' ' . implode(' AND ', $where);
        } else {
            return NULL;
        }
    }

    public static function toMapObject($key, $data = array())
    {
        $output = array();
        foreach ($data as $row) {
            $output[$row[$key]] = $row;
        }
        return $output;
    }

    public static function toMap($key, $val, $data = array())
    {
        $output = array();
        foreach ($data as $row) {
            $output[$row[$key]] = $row[$val];
        }
        return $output;
    }

    public static function devidedRound($divisior, $div, $round = 2)
    {
        return $div != 0 ? round($divisior / $div, $round) : null;
    }

    public static function dateNow()
    {
        return date('j F Y');
    }

    /*
     * Cek apakah kosong atau null
     * @param mixed $str
     * @return boolean
     */
    public static function isEmpty($str)
    {
        if (!isset($str) or trim($str) === '')
            return true;
        else
            return false;
    }

    //compare data array
    public static function isArrayDifferent(array $data1, array $data2, $specificParameter = null, $excludeParameter = null): bool
    {
        if ($specificParameter === null) {
            $specificParameter = array_keys($data1);
        }

        foreach ($specificParameter as $field) {
            if (isset($data1[$field]) && isset($data2[$field])) {
                if ($data1[$field] != $data2[$field]) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Unescape nested array
     * 
     * @param string|array|null $value
     * 
     * @return [type]
     */
    public static function unescapeDeep(string|array|null $value)
    {
        if (empty($value)) {
            return $value;
        }

        return is_array($value) ?
            array_map(function($v) {
                return self::unescapeDeep($v);
            }, $value) :
            html_entity_decode(strip_tags($value));
    }
}
