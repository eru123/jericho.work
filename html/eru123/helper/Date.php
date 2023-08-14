<?php

namespace eru123\helper;

use Exception;

define('DATE_TYPE_UNIT', 0);
define('DATE_TYPE_FORMAT', 1);
define('DATE_UNIT_MILLISECOND', 'ms');
define('DATE_UNIT_SECOND', 's');
define('DATE_UNIT_MINUTE', 'm');
define('DATE_UNIT_HOUR', 'h');
define('DATE_UNIT_DAY', 'd');
define('DATE_UNIT_WEEK', 'w');
define('DATE_UNIT_MONTH', 'M');
define('DATE_UNIT_YEAR', 'y');
define('DATE_UNIT_LEAP_YEAR', 'ly');

class Date
{
    /**
     * @var int $global_time static time value for time sensitive functions and simulations
     */
    private static $global_time;

    /**
     * Set global time
     * @param int $time
     */
    public static function setTime($time = null)
    {
        static::$global_time = $time ?? time();
    }

    /**
     * Get global time
     * @return int
     */
    public static function now()
    {
        if (is_null(static::$global_time)) {
            static::setTime();
        }
        return static::$global_time;
    }

    /**
     * translate unit name to unit keyword
     * @param string $unit Unit name
     * @return string|bool Returns unit keyword or false if unit name is invalid
     */
    public static function unit($unit)
    {
        $rgx_unit = [
            'ms' => '/^(\s+)?(ms|mils?|milliseconds?)?(\s+)?$/',
            's' => '/^(\s+)?(s|secs?|seconds?|timestamps?|ts)(\s+)?$/',
            'm' => '/^(\s+)?(m(in)?|mins?|minutes?)(\s+)?$/',
            'h' => '/^(\s+)?(h|hrs?|hours?)(\s+)?$/',
            'd' => '/^(\s+)?(d|days?)(\s+)?$/',
            'w' => '/^(\s+)?(w|weeks?)(\s+)?$/',
            'M' => '/^(\s+)?(M|months?)(\s+)?$/',
            'y' => '/^(\s+)?(y|years?)(\s+)?$/',
            'ly' => '/^(\s+)?(ly?|leaps?|leaps?\s?years?)(\s+)?$/',
        ];

        $unit = is_string($unit) && !empty(trim($unit)) ? trim($unit) : false;
        if ($unit) foreach ($rgx_unit as $key => $rgx) {
            if (preg_match($rgx, $unit)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Translate time to given unit
     * @param string $query Time to translate
     * @param string $out Unit to translate to
     * @return float|int time in $out
     */
    public static function translate($query, $out = "ms")
    {
        $out = static::unit($out);

        $rgx_time = [
            'ms' => '/^(\s+)?(\d+)(\s+)?(ms|mils?|milliseconds?)?(\s+)?$/',
            's' => '/^(\s+)?(\d+)(\s+)?(s|secs?|seconds?|timestamps?|ts)(\s+)?$/',
            'm' => '/^(\s+)?(\d+)(\s+)?(m(in)?|mins?|minutes?)(\s+)?$/',
            'h' => '/^(\s+)?(\d+)(\s+)?(h|hrs?|hours?)(\s+)?$/',
            'd' => '/^(\s+)?(\d+)(\s+)?(d|days?)(\s+)?$/',
            'w' => '/^(\s+)?(\d+)(\s+)?(w|weeks?)(\s+)?$/',
            'M' => '/^(\s+)?(\d+)(\s+)?(M|months?)(\s+)?$/',
            'y' => '/^(\s+)?(\d+)(\s+)?(y|years?)(\s+)?$/',
            'ly' => '/^(\s+)?(\d+)(\s+)?(ly?|leaps?|leaps?\s?years?)(\s+)?$/',
        ];

        $time = 0;
        $query = trim($query);
        foreach ($rgx_time as $key => $rgx) {
            if (preg_match($rgx, $query, $matches)) {
                $time = (float) $matches[2];
                switch ($key) {
                    case 'ms':
                        break;
                    case 's':
                        $time = $time * 1000;
                        break;
                    case 'm':
                        $time = $time * 60000;
                        break;
                    case 'h':
                        $time = $time * 3600000;
                        break;
                    case 'd':
                        $time = $time * 86400000;
                        break;
                    case 'w':
                        $time = $time * 604800000;
                        break;
                    case 'M':
                        $time = $time * 2592000000;
                        break;
                    case 'y':
                        $time = $time * 31536000000;
                        break;
                    case 'ly':
                        $time = $time * 31622400000;
                        break;
                }
                break;
            }
        }

        return static::ms_to($time, $out, DATE_TYPE_UNIT);
    }

    /**
     * Convert ms to other time unit
     * @param int $ms Milliseconds to convert
     * @param string $out Output unit to translate to
     * @param int $type Output type (`UNIT`|`FORMAT`)
     * @return float|int|string time in $out
     */
    public static function ms_to($ms, $out = "ms", $type = DATE_TYPE_UNIT)
    {
        $f_out = static::unit($out);
        $out = $type === DATE_TYPE_UNIT ? ($f_out ? $f_out : $out) : $out;

        $ms = floatval($ms);
        $opts = [
            'ms' => 1,
            's' => 1000,
            'm' => 60000,
            'h' => 3600000,
            'd' => 86400000,
            'w' => 604800000,
            'M' => 2592000000,
            'y' => 31536000000,
            'ly' => 31622400000,
        ];

        if (isset($opts[$out]) && $type === DATE_TYPE_UNIT) {
            return $ms / $opts[$out];
        } else if ($out == "datetime") {
            return date("Y-m-d H:i:s", $ms / 1000);
        } else if ($out == "date") {
            return date("Y-m-d", $ms / 1000);
        } else if ($out == "time") {
            return date("H:i:s", $ms / 1000);
        } else if ($type === DATE_TYPE_UNIT) {
            throw new Exception("Invalid output unit");
        }

        if ($type !== DATE_TYPE_FORMAT) {
            throw new Exception("Invalid output type");
        }

        return date($out, $ms / 1000);
    }

    /**
     * Date Time Magic Parser
     * @param string $query Date time to parse
     * @param string $out Unit or format to translate to
     * @return float|int|string time in $out
     */
    public static function parse(string $query, $out = "s", $type = DATE_TYPE_UNIT)
    {
        $now = static::now();

        $rgx_parser = [
            'tr_time' => [
                'rgx' => '/(\d+)(\s+)?([a-zM]+)(\s+)?/',
                'cb' => function ($matches) {
                    return static::translate($matches[0], 's');
                },
            ],
            'datetime' => [
                'rgx' => '/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/',
                'cb' => function ($matches) {
                    return strtotime($matches[0]);
                },
            ],
            'datetime_ns' => [
                'rgx' => '/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2})/',
                'cb' => function ($matches) {
                    return strtotime($matches[0] . ':00');
                },
            ],
            'datetime_nm' => [
                'rgx' => '/(\d{4})-(\d{2})-(\d{2})\s(\d{2})/',
                'cb' => function ($matches) {
                    return strtotime($matches[0] . ':00:00');
                },
            ],
            'date' => [
                'rgx' => '/(\d{4})-(\d{2})-(\d{2})/',
                'cb' => function ($matches) {
                    return strtotime($matches[0] . ' 00:00:00');
                },
            ],
            'month' => [
                'rgx' => '/(\d{4})-(\d{2})/',
                'cb' => function ($matches) {
                    return strtotime($matches[0] . '-01 00:00:00');
                },
            ],
            'time' => [
                'rgx' => '/(\d{2}):(\d{2}):(\d{2})/',
                'cb' => function ($matches) {
                    $nd = date('Y-m-d') . ' ' . $matches[0];
                    return strtotime($nd);
                },
            ],
            'time_ns' => [
                'rgx' => '/(\d{2}):(\d{2})/',
                'cb' => function ($matches) {
                    $nd = date('Y-m-d') . ' ' . $matches[0] . ':00';
                    return strtotime($nd);
                },
            ],
            'now' => [
                'rgx' => '/(now|time)/',
                'cb' => function () use ($now) {
                    return $now;
                },
            ],
            'today' => [
                'rgx' => '/(today|date)/',
                'cb' => function () use ($now) {
                    return strtotime(date('Y-m-d', $now));
                },
            ],
            'yesterday' => [
                'rgx' => '/yesterday/',
                'cb' => function () use ($now) {
                    return strtotime(date('Y-m-d', $now)) - 86400000;
                },
            ],
            'tomorrow' => [
                'rgx' => '/(tomorrow|tom)/',
                'cb' => function () use ($now) {
                    return strtotime(date('Y-m-d', $now)) + 86400000;
                },
            ],
            'after' => [
                'rgx' => '/after/',
                'cb' => function ($matches) {
                    return ' + ';
                },
            ],
            'before' => [
                'rgx' => '/before/',
                'cb' => function ($matches) {
                    return ' - ';
                },
            ],
            'ago' => [
                'rgx' => '/(\d+)(\s+)?ago/',
                'cb' => function ($matches) use ($now) {
                    return $now - $matches[1];
                },
            ],
        ];

        foreach ($rgx_parser as $rgx) {
            $query = preg_replace_callback($rgx['rgx'], $rgx['cb'], $query);
        }

        // check if safe to eval
        if (!preg_match('/^[\d\s\+\-\*\/\%\(\)]+$/', $query)) {
            throw new Exception('Invalid query');
        }

        $ts = eval('return ' . $query . ';') * 1000;

        return static::ms_to($ts, $out, $type);
    }
}
