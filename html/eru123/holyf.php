<?php

use eru123\config\BaseConfig;
use eru123\helper\ArrayUtil;
use eru123\helper\Composer;
use Composer\Autoload\ClassLoader;

function set_autoload(ClassLoader $autoload): void
{
    Composer::set_autoload($autoload);
}

function config(string $key, $default = null)
{
    return env($key, BaseConfig::get($key, $default));
}

function config_set(string $key, $value)
{
    return BaseConfig::set($key, $value);
}

function env(string $key = null, $default = null)
{
    return ArrayUtil::get($_ENV, $key, $default);
}

function env_set(string $key, $value)
{
    return ArrayUtil::set($_ENV, $key, $value);
}

function post(string $key = null, $default = null)
{
    return ArrayUtil::get($_POST, $key, $default);
}

function post_set(string $key, $value)
{
    return ArrayUtil::set($_POST, $key, $value);
}

function get(string $key = null, $default = null)
{
    return ArrayUtil::get($_GET, $key, $default);
}

function get_set(string $key, $value)
{
    return ArrayUtil::set($_GET, $key, $value);
}

function request(string $key = null, $default = null)
{
    return ArrayUtil::get($_REQUEST, $key, $default);
}

function server(string $key = null, $default = null)
{
    return ArrayUtil::get($_SERVER, $key, $default);
}

function server_set(string $key, $value)
{
    return ArrayUtil::set($_SERVER, $key, $value);
}

function session(string $key = null, $default = null)
{
    return ArrayUtil::get($_SESSION, $key, $default);
}

function session_set(string $key, $value)
{
    return ArrayUtil::set($_SESSION, $key, $value);
}

function cookie(string $key = null, $default = null)
{
    return ArrayUtil::get($_COOKIE, $key, $default);
}

function cookie_set(string $key, $value)
{
    return ArrayUtil::set($_COOKIE, $key, $value);
}

function files(string $key = null, $default = null)
{
    return ArrayUtil::get($_FILES, $key, $default);
}

function globals(string $key = null, $default = null)
{
    return ArrayUtil::get($GLOBALS, $key, $default);
}

function redirect(string $url, array $postdata = null, bool $replace = true, int $code = 302)
{
    if (empty($postdata)) {
        header('Location: ' . $url);
    }

    $qm = strpos($url, '?');
    if ($qm !== false && $qm < strlen($url) - 1) {
        $url .= '&';
    } else if ($qm === false) {
        $url .= '?';
    }

    $url .= http_build_query($postdata);
    header('location: ' . $url, $replace, $code);
    exit;
}

function out(...$msgs): void
{
    $stdout = fopen('php://stdout', 'w');
    foreach ($msgs as $msg) {
        fwrite($stdout, is_array($msg) || is_object($msg) ? print_r($msg, true) : (string) $msg);
    }
    fclose($stdout);
}
