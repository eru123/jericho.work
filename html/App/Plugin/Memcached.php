<?php

namespace App\Plugin;

use Memcache;

class MC {
    private $memcached = null;
    static $instance = null;

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __construct()
    {
    }
}