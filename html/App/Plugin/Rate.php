<?php

namespace App\Plugin;

use DateTime;

class Rate
{
    static $instance = null;
    private $mc = null;

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __construct()
    {
        $this->mc = MC::instance();
    }

    public function ip(string $mode, int $limit)
    {
        $d = 'YmdHis';
        $mode = substr($d, 0, strlen($d) - strpos(strrev($d), $mode));
        $date = date($mode);
        $ip = get_ip();
        $identifier = str_replace(['.', ':'], '_', $date . "_" . $ip);
        $count = $this->mc->get($identifier) ?? 0;

        $limited = false;

        if ($count >= $limit) {
            $limited = true;
        }

        out("IDENTIFIER: $identifier", PHP_EOL);

        $remaining = $limit - $count;
        $count += 1;

        switch ($mode) {
            case 'Y':
                $this->mc->set($identifier, $count, 31536000);
                break;
            case 'Ymd':
                $this->mc->set($identifier, $count, 86400);
                break;
            case 'YmdH':
                $this->mc->set($identifier, $count, 3600);
                break;
            case 'YmdHi':
                $this->mc->set($identifier, $count, 60);
                break;
            case 'YmdHis':
            default:
                $this->mc->set($identifier, $count, 1);
                break;
        }


        return [
            'limited' => $limited,
            'count' => $count,
            'limit' => $limit,
            'remaining' => $remaining > 0 ? $remaining : 0,
        ];
    }
}
