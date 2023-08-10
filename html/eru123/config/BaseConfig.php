<?php

namespace eru123\config;

use eru123\helper\ArrayUtil;
use eru123\helper\Composer;
use eru123\helper\StringUtil;
use ReflectionClass;

class BaseConfig
{
    protected static $classmaps = null;
    protected static $data = [];

    public static function get(string $key, mixed $default = null)
    {
        if (static::$classmaps === null) {
            static::$classmaps = [];
            foreach (Composer::get_classmap() as $class => $file) {
                if (is_subclass_of($class, BaseConfig::class)) {
                    $classname = explode('\\', $class);
                    $snake_case_classname = StringUtil::pascal_case_to_snake_case(end($classname));
                    static::$classmaps[$snake_case_classname] = $class;
                }
            }
        }
        
        $segments = explode('.', $key);
        $first_segment = array_shift($segments);

        if (empty($first_segment) || !isset(static::$classmaps[$first_segment])) {
            return $default;
        }

        if (count($segments) > 0 && !isset(static::$data[$first_segment])) {
            static::$data[$first_segment] = [];
        } else if (count($segments) === 0 && isset(static::$data[$first_segment])) {
            return static::$data[$first_segment];
        } else if (count($segments) === 0) {
            return $default;
        }

        $reflection = new ReflectionClass(static::$classmaps[$first_segment]);
        $second_segment = array_shift($segments);

        if ($reflection->hasProperty($second_segment) && $reflection->getProperty($second_segment)->isStatic()) {
            $property = $reflection->getProperty($second_segment);
            $property->setAccessible(true);
            static::$data[$first_segment][$second_segment] = $property->getValue();
        } else if ($reflection->hasMethod($second_segment) && $reflection->getMethod($second_segment)->isStatic()) {
            $method = $reflection->getMethod($second_segment);
            $method->setAccessible(true);
            static::$data[$first_segment][$second_segment] = $method->invoke(null);
        } else {
            return $default;
        }

        if (count($segments) > 0) {
            return is_array(static::$data[$first_segment][$second_segment]) ? ArrayUtil::get(static::$data[$first_segment][$second_segment], implode('.', $segments), $default) : $default;
        }

        return static::$data[$first_segment][$second_segment];
    }

    public static function set(string $key, mixed $value = null)
    {
        if (static::$classmaps === null) {
            static::$classmaps = [];
            foreach (Composer::get_classmap() as $class => $file) {
                if (is_subclass_of($class, BaseConfig::class)) {
                    $classname = explode('\\', $class);
                    $snake_case_classname = StringUtil::pascal_case_to_snake_case(end($classname));
                    static::$classmaps[$snake_case_classname] = $class;
                }
            }
        }
        
        $segments = explode('.', $key);
        $first_segment = array_shift($segments);

        if (empty($first_segment) || !isset(static::$classmaps[$first_segment])) {
            return false;
        }

        if (count($segments) > 0 && !isset(static::$data[$first_segment])) {
            static::$data[$first_segment] = [];
        } else if (count($segments) === 0 && isset(static::$data[$first_segment])) {
            return false;
        } else if (count($segments) === 0) {
            return false;
        }

        $reflection = new ReflectionClass(static::$classmaps[$first_segment]);
        $second_segment = array_shift($segments);

        if ($reflection->hasProperty($second_segment)) {
            $property = $reflection->getProperty($second_segment);
            $property->setAccessible(true);
            $property->setValue($value);
        } else if ($reflection->hasMethod($second_segment)) {
            $method = $reflection->getMethod($second_segment);
            $method->setAccessible(true);
            $method->invoke(null, $value);
        } 

        if (count($segments) > 0) {
            return is_array(static::$data[$first_segment][$second_segment]) ? ArrayUtil::set(static::$data[$first_segment][$second_segment], implode('.', $segments), $value) : false;
        }

        return static::$data[$first_segment][$second_segment] = $value;
    }
}