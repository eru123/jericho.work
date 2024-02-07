<?php

use eru123\router\Router;

$cdn = new Router();
// $cdn->base('/cdn');
// $cdn->error(function (Throwable $e) {
//     $code = intval($e->getCode() ?: 500);
//     return [
//         'status' => false,
//         'code' => $code,
//         'error' => $e->getMessage()
//     ];
// });

// $cdn->post('/upload', 'App\Controller\CDN@upload');
// $cdn->get('/stream/$id/$name', 'App\Controller\CDN@stream');
// $cdn->get('/download/$id/$name', 'App\Controller\CDN@download');
// $cdn->get('/', 'App\Controller\CDN@index');
// $cdn->static('/', [__CLIENT__ . '/cdn/dist/cdn'], [], 'App\Controller\CDN::index');

return $cdn;
