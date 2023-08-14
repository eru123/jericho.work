<?php

namespace eru123\config;

use Exception;

class DotEnv
{
    public static function load(string $path, bool $strict = false): void
    {
        $path = realpath($path);

        if ($path !== false && is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
            $path = realpath($path);
        }

        if ($path === false) {
            !$strict || throw new Exception("Environment file not found");
            return;
        }

        try {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        } catch (Exception $e) {
            !$strict || throw new Exception('File not readable: ' . $e->getMessage());
            return;
        }

        foreach ($lines as $line) {
            try {
                $sym = ['//', '--', '#', ';'];
                $ch0 = strlen($line) > 0 ? substr(trim($line), 0, 1) : false;
                foreach ($sym as $s) {
                    if ($ch0 === $s) {
                        continue 2;
                    }
                }

                env_set(...static::parse($line, $strict));
            } catch (Exception $e) {
                !$strict || throw new Exception('Error parsing .env file: ' . $e->getMessage());
                return;
            }
        }
    }

    public static function parse(string $line, bool $strict = false): array
    {
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($strict && preg_match('/[^a-z0-9_.]/i', $name)) {
            throw new Exception("Invalid environment variable name: {$name}");
        }

        if (preg_match('/^(true|false|null|\d+|\d+.\d+)$/i', $value)) {
            return [$name, json_decode(strtolower($value))];
        }

        $value = preg_replace_callback('/\${([a-z0-9_.]+)}/i', function ($matches) use ($strict) {
            if (is_null(env($matches[1])) && $strict) {
                throw new Exception("Environment variable [{$matches[1]}] not found.");
            }

            return env($matches[1], '');
        }, $value);

        if (preg_match('/^"(.+)"$/', $value)) {
            $value = preg_replace('/^"(.+)"$/', '$1', $value);
        } elseif (preg_match("/^'(.+)'$/", $value)) {
            $value = preg_replace("/^'(.+)'$/", '$1', $value);
        }

        return [$name, $value];
    }
}
