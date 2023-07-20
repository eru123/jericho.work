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

$router->static('/', [__DIR__ . '/../../client/admin/dist'], [], 'App\Controller\Admin::index');
return $router;
