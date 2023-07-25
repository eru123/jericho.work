<?php

namespace App\Plugin;

class Daemon
{
    private $mc_keys = [];
    private $mc = null;
    private $us = 10000;
    private $pid = null;
    private $stop = false;
    private $cycle = 0;
    private $lcycle = 0;
    private $time = null;
    private $last_time = null;
    private $mem = 0;
    private $malloc = 0;
    protected $data = [];

    public function __construct()
    {
        $this->mc = MC::instance()->obj();
        $this->pid = getmypid();
    }

    public function time()
    {
        return $this->time ?? time();
    }

    public function last_time()
    {
        return $this->last_time ?? time();
    }

    public function us(int $us)
    {
        $this->us = $us;
    }

    public function cycle()
    {
        return $this->cycle ?? 0;
    }

    public function musage()
    {
        return $this->mem ?? 0;
    }

    public function malloc()
    {
        return $this->malloc ?? 0;
    }

    public function pid()
    {
        return $this->pid;
    }

    public function usleep(int $us)
    {
        usleep($us);
    }

    public function __destruct()
    {
        $this->mem_flush();
    }

    public function data()
    {
        return $this->data;
    }

    public function __get(string $key)
    {
        if (in_array($key, $this->data, true)) {
            return $this->data[$key];
        }

        return null;
    }

    public function __set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    public function mem_set(string $key, $value, int $expire = 0)
    {
        if (!in_array($key, $this->mc_keys, true)) {
            $this->mc_keys[] = $key;
        }

        return $this->mc->set($key, $value, $expire);
    }

    public function mem_get(string $key)
    {
        return $this->mc->get($key);
    }

    public function mem_flush()
    {
        foreach ($this->mc_keys as $key) {
            $this->mc->delete($key);
        }
    }

    public function mem_limit(string $limit = '128M')
    {
        ini_set('memory_limit', $limit);
    }

    public function mem_delete(string $key)
    {
        return $this->mc->delete($key);
    }

    public function stop()
    {
        $this->stop = true;
    }

    function is_second_new()
    {
        return $this->time !== $this->last_time;
    }

    function is_second(int $s = 0)
    {
        if (!$this->is_second_new()) return false;
        return $this->time % 60 === $s;
    }

    public function run(array $callbacks)
    {
        $daemon_pid = $this->mem_get('daemon_pid');
        if ($daemon_pid) {
            if (posix_kill($daemon_pid, 0)) {
                echo "Daemon is already running with PID: " . $daemon_pid . PHP_EOL;
                exit;
            }
        }

        unset($daemon_pid);
        $this->mem_set('daemon_pid', $this->pid);
        $this->stop = false;
        $this->time = time();
        $this->last_time = $this->time;
        $this->cycle = 0;
        $this->lcycle = 0;
        $this->mem = memory_get_usage() / 1024 / 1024;
        $this->malloc = memory_get_usage(true) / 1024 / 1024;

        while (!$this->stop) {
            $this->lcycle++;
            $this->time = time();

            if ($this->is_second_new()) {
                $this->mem = memory_get_usage() / 1024 / 1024;
                $this->malloc = memory_get_usage(true) / 1024 / 1024;
                $this->cycle = $this->lcycle;
                $this->lcycle = 1;
            }

            foreach ($callbacks as $callback) {
                $cb = Callback::make($callback);
            
                if (is_callable($cb)) {
                    call_user_func_array($callback, [&$this]);
                }
            }

            $this->last_time = $this->time;
            usleep($this->us);
        }

        $this->mem_flush();
    }
}
