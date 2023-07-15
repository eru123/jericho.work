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

vite($router, '/', env('CDN_PROD', true), [
    'entry' => 'src/main.js',
    'dist' => __DIR__ . '/../../client/admin/dist',
    'favicon' => 'favicon.ico',
], 'App\Controller\CDN::index');

return $router;
