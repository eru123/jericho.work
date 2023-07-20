<?php

use eru123\helper\ArrayUtil as A;
use eru123\router\Context;
use eru123\router\Router;
use eru123\router\Helper as RouterHelper;
use App\Plugin\Vite;

function base_url($path = '')
{
    $base_url = env('BASE_URL', '/');
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

function cdn_stream(string $id, string $name = null)
{
    $cdn_url = env('BASE_URL') . '/cdn';
    return rtrim($cdn_url, '/') . "/stream/$id" . ($name ? "/$name" : '');
}

function cdn_download(string $id, string $name = null)
{
    $cdn_url = env('BASE_URL') . '/cdn';
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

function vite(Router &$router, string $base, bool $prod, array $data = [], $callback = null)
{
    if (!$callback) {
        $callback = function () {
            return null;
        };
    }

    $base = rtrim($base, '/');
    $router->base($base);

    $forbidden_files = [
        '/manifest.json',
        '/index.html',
    ];

    // $base = empty($base) ? '/' : $base;

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
    $favicon = A::get($data, 'favicon');

    $vite = Vite::instance();
    $vite->template($template);

    $favicon = $favicon ? rtrim($base, "/") . "/" . ltrim($favicon, "/") : null;
    $favicon_html = $favicon ? "<link rel=\"icon\" href=\"$favicon\">" : "";
    $vite->header($favicon_html);

    $vite->seo([
        'title' => $app_title,
        'description' => $app_title,
        'image' => $favicon,
        'url' => base_url(RouterHelper::uri()),
    ]);

    if (!$prod) {
        $vite->data([
            'base' => $base,
            'entry' => $entry,
            'client' => $client,
            'app_title' => $app_title,
            'app_id' => $app_id,
        ]);

        $router->static('/src', [$src]);
        $router->static('/', [$public], [], $callback, function (Context $c) use ($forbidden_files, $vite) {
            if (in_array($c->file, $forbidden_files)) {
                return false;
            }

            if (!$c->file_path) {
                http_response_code(200);
                return $vite->build();
            }
        });

        $router->get('/', $callback, (function () use ($vite) {
            http_response_code(200);
            return $vite->build();
        }));

        return;
    }

    $dist = rtrim(A::get($data, 'dist'), '/');

    $css = [];
    $manifest = json_decode(file_get_contents(rtrim($dist, '/') . '/manifest.json'), true);

    if (isset($manifest[$entry]) && isset($manifest[$entry]['isEntry']) && $manifest[$entry]['isEntry'] === true) {
        $css = isset($manifest[$entry]['css']) ? $manifest[$entry]['css'] : [];
        $entry = $manifest[$entry]['file'];
    }

    $vite->headers(array_map(function ($css) use ($base) {
        return '<link rel="stylesheet" href="' . rtrim($base, '/') . '/' . $css . '">';
    }, $css));

    $vite->data([
        'base' => rtrim($base, '/'),
        'app_title' => $app_title,
        'app_id' => $app_id,
        'entry' => $entry,
    ]);

    $router->static('/', [$dist], [], $callback, function (Context $c) use ($forbidden_files, $vite) {
        if (in_array($c->file, $forbidden_files)) {
            return false;
        }

        if (!$c->file_path) {
            http_response_code(200);
            return $vite->build();
        }
    });
}

function is_dev()
{
    return in_array(trim(strtolower(env('APP_ENV', 'production'))), ['dev', 'development']);
}

function append_file($file, $content)
{
    $f = realpath($file);
    if (!$f || !file_exists($f)) {
        return false;
    }
    $handle = fopen($f, 'a');
    fwrite($handle, $content);
}

function prepend_file($file, $content)
{
    $f = realpath($file);
    if (!$f || !file_exists($f)) {
        return false;
    }
    $handle = fopen($f, 'r+');
    $len = strlen($content);
    $final_len = filesize($f) + $len;
    $cache_old = fread($handle, $len);
    rewind($handle);
    $i = 1;
    while (ftell($handle) < $final_len) {
        fwrite($handle, $content);
        $content = $cache_old;
        $cache_old = fread($handle, $len);
        fseek($handle, $i * $len);
        $i++;
    }
    return true;
}

function writelog($content, $append = true, $file = null)
{
    $file = $file ?? __DIR__ . '/../logs/' . date('Y-m-d') . '.log';
    if (!file_exists($file)) {
        touch($file);
    }

    if (is_array($content) || is_object($content)) {
        $content = json_encode($content, JSON_PRETTY_PRINT);
    }
    $msg = "[\033[34m" . date("Y-m-d H:i:s") . "\033[0m] $content\n";
    if (!is_writable($file)) {
        return false;
    }

    if ($append) {
        return append_file($file, $msg);
    }

    return prepend_file($file, $msg);
}
