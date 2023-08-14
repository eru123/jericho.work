<?php

namespace eru123\helper;

class ClassUtil {
    public static function parent_history(string $class) {
        $classes = [];

        if (!class_exists($class)) {
            return $classes;
        }

        while ($class = get_parent_class($class)) {
            $classes[] = $class;
        }

        return $classes;
    }

    public static function has_parent_of(string $class, string $parent) {
        if (!class_exists($parent)) {
            return false;
        }
        
        return in_array($parent, self::parent_history($class));
    }
}