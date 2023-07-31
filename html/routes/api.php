<?php

use App\Plugin\DB;
use eru123\router\Router;
use Throwable;

$api = new Router();
$api->base('/api');

$api->response(function ($data) {
    if (is_array($data) xor is_object($data)) {
        headers_sent() or header('Content-Type: application/json');
        $data = (array) $data;
        $res = [];

        if (env('APP_ENV') === 'development') {
            if (!isset($data['debug'])) {
                $data['debug'] = [];
            }

            $data['debug']['response_debug'] = true;
            $data['debug']['db_query'] = DB::instance()->queryHistory();
            $data['debug']['memory'] = [
                'usage' => ceil(memory_get_usage() / 1024 / 1024) . 'MB',
                'usage_alloc' => ceil(memory_get_usage(true) / 1024 / 1024) . 'MB',
                'peak' => ceil(memory_get_peak_usage() / 1024 / 1024) . 'MB',
                'peak_alloc' => ceil(memory_get_peak_usage(true) / 1024 / 1024) . 'MB',
            ];
        } else {
            unset($data['debug']);
        }

        try {
            $res = json_encode($data, 0, JSON_ERROR_DEPTH | JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $res = json_encode([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 0, JSON_ERROR_DEPTH | JSON_THROW_ON_ERROR);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            $res = json_encode([
                'error' => json_last_error_msg(),
                'code' => json_last_error(),
            ], 0);
        }

        echo $res;
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
    http_response_code((int) $e->getCode());
    $res = [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
    ];

    if (env('APP_ENV') === 'development') {
        $res['debug']['error'] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ];
    }

    return $res;
});

$v1 = require __ROUTES__ . '/api/v1.php';

$api->child($v1);
return $api;
