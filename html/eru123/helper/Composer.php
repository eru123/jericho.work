<?php

namespace eru123\helper;

use Composer\Autoload\ClassLoader;
use Exception;

class Composer
{
    protected static $autoload = null;
    protected static $composer_path = null;
    protected static $classmap = null;
    protected static $config_classes = null;

    public static function set_composer_path(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception("Composer path does not exist: $path");
        }

        static::$composer_path = realpath($path);
    }

    public static function get_composer_path(): string
    {
        return static::$composer_path;
    }

    public static function get_autoload(): ClassLoader
    {
        if (static::$autoload === null && static::$composer_path === null) {
            static::init();
        } else if (static::$autoload === null) {
            static::$autoload = require_once static::$composer_path;
        }

        return static::$autoload;
    }

    public static function set_autoload(ClassLoader $autoload): void
    {
        static::$autoload = $autoload;
    }

    public static function get_classmap(): array
    {
        if (static::$classmap === null) {
            static::$classmap = [];
            $classmaps = static::get_autoload()->getClassMap();
            foreach ($classmaps as $class => $file) {
                $classfile = realpath($file);
                if ($classfile !== false) {
                    static::$classmap[$class] = $classfile;
                }
            }
        }

        return static::$classmap;
    }

    public static function init(): void
    {
        static::$autoload = new ClassLoader();
    }
}
