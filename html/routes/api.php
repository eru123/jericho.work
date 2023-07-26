<?php

use eru123\router\Router;
use Throwable;

$api = new Router();
$api->base('/api');

$api->response(function ($data) {
    if (is_array($data) xor is_object($data)) {
        headers_sent() or header('Content-Type: application/json');
        try {
            echo json_encode($data, 0, JSON_ERROR_DEPTH | JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            echo json_encode([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 0);
        }
        exit;
    }

    echo $data;
    exit;
});

$api->fallback(function () {
    http_response_code(404);
    return [
        'error' => 'API not found',
        'code' => 404,
    ];
});

$api->error(function (Throwable $e) {
    http_response_code($e->getCode());
    return [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
    ];
});

$v1 = require __ROUTES__ . '/api/v1.php';

$api->child($v1);
return $api;
