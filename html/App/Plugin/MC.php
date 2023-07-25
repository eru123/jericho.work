<?php

namespace App\Plugin;

use Memcached;

/**
 * Memcached wrapper
 */
class MC
{
    static $pool = [];
    static $instance = null;
    private  $id;

    public static function instance(?string $id = null)
    {
        if (is_null($id)) $id = env('APP_ENV', 'development');
        if (static::$instance === null) {
            static::$instance = new static($id);
        }
        return static::$instance;
    }

    public function __construct(string $id = 'development')
    {
        $this->id = $id;
        if (!isset(static::$pool[$id])) {
            static::$pool[$id] = new Memcached($id);
            if (!count(static::$pool[$id]->getServerList())) {
                static::$pool[$id]->addServer(getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT'));
            }
        }
    }

    public function __get(string $key)
    {
        return static::$pool[$this->id]->get($key) ?? null;
    }

    public function get(string $key)
    {
        return static::$pool[$this->id]->get($key) ?? null;
    }

    public function __set(string $key, $value)
    {
        return static::$pool[$this->id]->set($key, $value);
    }

    public function set(string $key, $value, int $expire = 0)
    {
        return static::$pool[$this->id]->set($key, $value, $expire);
    }

    public function delete(string $key)
    {
        return static::$pool[$this->id]->delete($key);
    }

    public function flush()
    {
        return static::$pool[$this->id]->flush();
    }

    public function flushAll()
    {
        foreach (static::$pool as $mc) {
            $mc->flush();
        }
    }

    public function obj(): Memcached
    {
        return static::$pool[$this->id];
    }
}
