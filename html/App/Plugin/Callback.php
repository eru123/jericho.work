<?php

namespace App\Plugin;

use Exception;

class Callback {
    /**
     * Convert to a recognizable callback
     * @param callable|string|array|Closure $callback
     * @return callable
     */
    public static function make($cb)
    {
        if (is_callable($cb)) {
            return $cb;
        }

        if (is_string($cb)) {
            $rgx = '/^([a-zA-Z0-9_\\\\]+)(::|@)([a-zA-Z0-9_]+)$/';
            if (preg_match($rgx, $cb, $matches)) {
                $classname = $matches[1];
                $method = $matches[3];
                if (class_exists($classname)) {
                    $obj = new $classname();
                    if (method_exists($obj, $method)) {
                        return [$obj, $method];
                    }
                }
            }
        }

        if (is_array($cb) && count($cb) == 2) {
            if (is_object($cb[0]) && is_string($cb[1])) {
                return $cb;
            } else if (is_string($cb[0]) && is_string($cb[1])) {
                $classname = $cb[0];
                $method = $cb[1];
                if (class_exists($classname)) {
                    $obj = new $classname();
                    if (method_exists($obj, $method)) {
                        return [$obj, $method];
                    } else if (method_exists($classname, $method)) {
                        return $cb;
                    }
                }
            }
        }

        throw new Exception('invalid callback');
    }
}