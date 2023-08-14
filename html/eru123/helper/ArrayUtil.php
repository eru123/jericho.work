<?php

namespace eru123\helper;

class ArrayUtil
{
    public static function get(array $array, string $key = null, $default = null)
    {   
        if (is_null($key) || empty($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        if (preg_replace_callback('/\{([^\}]+)\}/', function ($matches) use (&$array) {
            $key = $matches[1];
            $value = self::get($array, $key);
            if (is_array($value)) {
                $value = self::get($value, $key);
            }
            return $value;
        }, $key) !== $key) {
            $key = preg_replace_callback('/\{([^\}]+)\}/', function ($matches) use (&$array) {
                $key = $matches[1];
                $value = self::get($array, $key);
                if (is_array($value)) {
                    $value = self::get($value, $key);
                }
                return $value;
            }, $key);
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    public static function set(array &$array, string $key, $value)
    {
        if (is_null($key) || empty($key)) {
            return $array = $value;
        }

        if (preg_replace_callback('/\{([^\}]+)\}/', function ($matches) use (&$array) {
            $key = $matches[1];
            $value = self::get($array, $key);
            if (is_array($value)) {
                $value = self::get($value, $key);
            }
            return $value;
        }, $key) !== $key) {
            $key = preg_replace_callback('/\{([^\}]+)\}/', function ($matches) use (&$array) {
                $key = $matches[1];
                $value = self::get($array, $key);
                if (is_array($value)) {
                    $value = self::get($value, $key);
                }
                return $value;
            }, $key);
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    public static function has(array $array, string $key)
    {
        if (empty($array) || is_null($key) || empty($key)) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }

        return true;
    }
}
