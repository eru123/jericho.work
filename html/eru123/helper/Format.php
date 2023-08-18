<?php

namespace eru123\helper;

use eru123\types\Number;

define('FORMAT_NUMBER_BYTE', 0);
define('FORMAT_NUMBER_BIT', 1);
define('FORMAT_NUMBER_UNIT', 2);
define('FORMAT_NUMBER_PRECISION', 3);
define('FORMAT_TEMPLATE_DOLLAR_CURLY', 4);
define('FORMAT_TEMPLATE_DOLLAR', 5);
define('FORMAT_TEMPLATE_CURLY', 6);
define('FORMAT_TEMPLATE_PERCENT', 7);
define('FORMAT_TEMPLATE_PERCENT_CURLY', 8);
define('FORMAT_TEMPLATE_COLON', 9);
define('FORMAT_TEMPLATE_LEFT_COLON', 10);
define('FORMAT_TEMPLATE_RIGHT_COLON', 11);
define('FORMAT_TEMPLATE_DOUBLE_LEFT_COLON', 12);
define('FORMAT_TEMPLATE_DOUBLE_RIGHT_COLON', 13);
define('FORMAT_TEMPLATE_DOUBLE_COLON', 14);
define('FORMAT_EVAL_TEMPLATE_CURLY', 15);
define('FORMAT_EVAL_TEMPLATE_DOLLAR', 16);

class Format
{
    public static function number(string $int, int $flag = FORMAT_NUMBER_UNIT, int $precision = 0, bool $trailing_zero = false)
    {
        $bytes_units = [
            'YB' => 1208925819614629174706176,
            'ZB' => 1180591620717411303424,
            'EB' => 1152921504606846976,
            'PB' => 1125899906842624,
            'TB' => 1099511627776,
            'GB' => 1073741824,
            'MB' => 1048576,
            'KB' => 1024,
            'B' => 1,
        ];

        $bits_units = [
            'Yb' => 1208925819614629174706176,
            'Zb' => 1180591620717411303424,
            'Eb' => 1152921504606846976,
            'Pb' => 1125899906842624,
            'Tb' => 1099511627776,
            'Gb' => 1073741824,
            'Mb' => 1048576,
            'Kb' => 1024,
            'b' => 1,
        ];

        $units = [
            'Y' => 1000000000000000000000000,
            'Z' => 1000000000000000000000,
            'E' => 1000000000000000000,
            'P' => 1000000000000000,
            'T' => 1000000000000,
            'B' => 1000000000,
            'M' => 1000000,
            'K' => 1000,
        ];

        switch ($flag) {
            case FORMAT_NUMBER_BYTE:
                foreach ($bytes_units as $unit => $value) {
                    if ($int >= $value) {
                        return $trailing_zero ? Number::div($int, $value, $precision) . $unit : preg_replace('/\.?0+$/', '', Number::div($int, $value, $precision)) . $unit;
                    }
                }
                return $int;
            case FORMAT_NUMBER_BIT:
                foreach ($bits_units as $unit => $value) {
                    if ($int >= $value) {
                        return $trailing_zero ? Number::div($int, $value, $precision) . $unit : preg_replace('/\.?0+$/', '', Number::div($int, $value, $precision)) . $unit;
                    }
                }
                return $int;
            case FORMAT_NUMBER_UNIT:
                foreach ($units as $unit => $value) {
                    if ($int >= $value) {
                        return $trailing_zero ? Number::div($int, $value, $precision) . $unit : preg_replace('/\.?0+$/', '', Number::div($int, $value, $precision)) . $unit;
                    }
                }
                return $int;
            case FORMAT_NUMBER_PRECISION:
                return $trailing_zero ? Number::round($int, $precision) : preg_replace('/\.?0+$/', '', Number::round($int, $precision));
            default:
                return $int;
        }
    }

    public static function template(string $template, array $params = [], int $flag = FORMAT_TEMPLATE_DOLLAR_CURLY)
    {
        $rgx = match ($flag) {
            FORMAT_TEMPLATE_DOLLAR => '/\$(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?/',
            FORMAT_TEMPLATE_CURLY => '/\{(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?\}/',
            FORMAT_TEMPLATE_PERCENT => '/%(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?%/',
            FORMAT_TEMPLATE_PERCENT_CURLY => '/%\{(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?\}%/',
            FORMAT_TEMPLATE_COLON => '/:(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?:/',
            FORMAT_TEMPLATE_LEFT_COLON => '/:(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?/',
            FORMAT_TEMPLATE_RIGHT_COLON => '/(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?:/',
            FORMAT_TEMPLATE_DOUBLE_LEFT_COLON => '/::(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?/',
            FORMAT_TEMPLATE_DOUBLE_RIGHT_COLON => '/(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?::/',
            FORMAT_TEMPLATE_DOUBLE_COLON => '/::(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?::/',
            FORMAT_TEMPLATE_DOLLAR_CURLY => '/\$\{(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?\}/',
            default => '/\$\{(\s+)?([a-zA-Z]([a-zA-Z0-9_]+)?)(\s+)?\}/',
        };

        $matches = [];
        preg_match_all($rgx, $template, $matches);
        $keys = $matches[2];
        $values = [];
        foreach ($keys as $key) {
            $values[] = isset($params[$key]) ? $params[$key] : '';
        }
        return str_replace($matches[0], $values, $template);
    }

    public static function eval_template(string $template, array $params = [], int $flag = FORMAT_EVAL_TEMPLATE_DOLLAR)
    {
        $rgx = match ($flag) {
            FORMAT_EVAL_TEMPLATE_DOLLAR => '/\$\{\{(.*?)\}\}/',
            FORMAT_EVAL_TEMPLATE_CURLY => '/\{\{(.*?)\}\}/',
            default => '/\$\{\{(.*?)\}\}/',
        };
        $matches = [];
        preg_match_all($rgx, $template, $matches);
        extract($params);
        $values = [];
        foreach ($matches[1] as $match) {
            $values[] = eval('return ' . $match . ';');
        }
        return str_replace($matches[0], $values, $template);
    }
}
