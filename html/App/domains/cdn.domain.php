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

$router->static('/', [__DIR__ . '/../../client/cdn/dist'], [], 'App\Controller\CDN::index');

return $router;
