<?php

use eru123\router\Router;

$router = new Router();
$router->error(function (Throwable $e) {
    $code = intval($e->getCode() ?: 500);
    return [
        'status' => false,
        'code' => $code,
        'error' => $e->getMessage()
    ];
});

$router->post('/upload', 'App\Controller\CDN@upload');
$router->get('/stream/$id/$name', 'App\Controller\CDN@stream');
$router->get('/download/$id/$name', 'App\Controller\CDN@download');

vite($router, '/', env('CDN_PROD', true), [
    'entry' => 'src/main.js',
    // 'client' => 'http://127.0.0.1:3000',
    // 'public' => __DIR__ . '/../../client/cdn/public',
    // 'src' => __DIR__ . '/../../client/cdn/src',
    'dist' => __DIR__ . '/../../client/cdn/dist',
    'favicon' => 'favicon.ico',
], 'App\Controller\CDN::index');

return $router;
