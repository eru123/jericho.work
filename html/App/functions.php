<?php

use eru123\helper\ArrayUtil as A;
use eru123\helper\Format;
use eru123\router\Context;
use eru123\router\Router;

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
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip ?? '0';
}

function vite(Router &$router, string $base, bool $prod, array $data = [])
{
    error_log($base);
    $forbidden_files = [
        '/manifest.json',
        '/index.html',
    ];

    $base = rtrim($base, '/');
    $base = empty($base) ? '/' : $base;

    $app_title = A::get($data, 'title', 'CDN');
    $entry = A::get($data, 'entry', 'src/main.js');
    $client = rtrim(A::get($data, 'client', '/'), '/');
    $react = A::get($data, 'react', false);
    $template_name = $prod ? 'vite' : ($react ? 'dev-react' : 'dev-vite');
    $template_path = realpath(__DIR__ . '/../client/template/' . $template_name . '.html');
    $template = file_get_contents($template_path);
    $app_id = A::get($data, 'id', 'app');
    $public = A::get($data, 'public');
    $src = A::get($data, 'src');

    if (!$prod) {
        $html = Format::template($template, [
            'client' => $client,
            'base' => $base,
            'entry' => $entry,
            'app_title' => $app_title,
            'app_id' => $app_id,
        ], FORMAT_TEMPLATE_DOLLAR_CURLY);

        $router->static($base, [$public]);
        $router->static(rtrim($base, '/') . '/src', [$src]);

        $router->get('/', (function () use ($html) {
            http_response_code(200);
            return $html;
        }));

        return;
    }

    $dist = rtrim(A::get($data, 'dist'), '/');
    $router->static($base, [$dist], [], function (Context $c) use ($forbidden_files) {
        if (in_array($c->file, $forbidden_files)) {
            return false;
        }
    });

    $css = [];
    $manifest = json_decode(file_get_contents(rtrim($dist, '/') . '/manifest.json'), true);

    if (isset($manifest[$entry]) && isset($manifest[$entry]['isEntry']) && $manifest[$entry]['isEntry'] === true) {
        $css = isset($manifest[$entry]['css']) ? $manifest[$entry]['css'] : [];
        $entry = $manifest[$entry]['file'];
    }


    $html = Format::template($template, [
        'base' => rtrim($base, '/'),
        'app_title' => $app_title,
        'app_id' => $app_id,
        'entry' => $entry,
        'headers' => implode("", array_map(function ($css) use ($base) {
            return '<link rel="stylesheet" href="' . rtrim($base, '/') . '/' . $css . '">';
        }, $css)),
    ], FORMAT_TEMPLATE_DOLLAR_CURLY);

    $router->fallback(function () use ($html) {
        http_response_code(200);
        return $html;
    });
}
