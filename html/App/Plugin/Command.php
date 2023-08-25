<?php

namespace App\Plugin;

class Command
{
    static $cmds = [];
    public static function register($cmd, $callback)
    {
        static::$cmds[$cmd] = Callback::make($callback);
    }

    public static function run($cmd, $args = [])
    {
        if (isset(static::$cmds[$cmd])) {
            $callback = static::$cmds[$cmd];
            return call_user_func_array($callback, $args);
        }
    }

    public static function exec($cmd, $args = [])
    {
        return static::run($cmd, $args);
    }

    public static function exists($cmd)
    {
        return isset(static::$cmds[$cmd]);
    }
}
