<?php

function base_url($path = '')
{
    $base_url = env('BASE_URL', 'http://localhost');
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

function cdn_stream(string $id, string $name = null)
{
    $cdn_url = env('CDN_URL', 'http://localhost/cdn');
    return rtrim($cdn_url, '/') . "/stream/$id" . ($name ? "/$name" : '');
}

function cdn_download(string $id, string $name = null)
{
    $cdn_url = env('CDN_URL', 'http://localhost/cdn');
    return rtrim($cdn_url, '/') . "/download/$id" . ($name ? "/$name" : '');
}

function get_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['f'];
    }
    return $ip ?? '0';
}
