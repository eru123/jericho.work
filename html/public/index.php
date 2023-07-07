<?php

// show all errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

ini_set('memory_limit', '256M');

require_once __DIR__ . '/../vendor/autoload.php';

use eru123\router\Router;
use eru123\router\Context;
use eru123\config\DotEnv;
use eru123\helper\ArrayUtil as A;
use eru123\helper\Format;

DotEnv::load(__DIR__ . '/../');

$router = new Router();
$router->error(function (Throwable $e) {
    $code = intval($e->getCode() ?: 500);
    // $code = 500;
    return Router::status_page($code, $code . ' Internal Server Error', 'The server encountered an internal error and was unable to complete your request. Either the server is overloaded or there is an error in the application.');
    // http_response_code($code);
    // return [
    //     'code' => $code,
    //     'message' => $e->getMessage(),
    // ];
    // return Router::status_page($code, $code . ' Internal Server Error', $e->getMessage());
    // return Router::status_page($code, $code . ' Internal Server Error', $e->getMessage().'::'.$e->getTraceAsString());
    // return [
    //     'trace' => $e->getTrace(),
    //     'message' => $e->getMessage(),
    //     'code' => $e->getCode(),
    // ];
});
$router->get('/info', function (Context $c) {
    phpinfo();
    exit;
});

$router->static('/', __DIR__, [], function (Context $c) {
    // throw new Exception('404 Not Found sdfsdf', 404);
    if ($c->file == '/index.php' || substr($c->file, -4) == '.php') {
        return false;
    }
});

vite($router, '/cdn', true, [
    'entry' => 'src/main.js',
    'client' => 'http://127.0.0.1:3000',
    'public' => __DIR__ . '/../client/cdn/public',
    'src' => __DIR__ . '/../client/cdn/src',
    'dist' => __DIR__ . '/../client/cdn/dist'
]);

// if server name is cdn.jericho.work
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == env('CDN_DOMAIN')) {


    // $router->get('/stream/$id', 'App\Controller\CDN@stream');
    $router->get('/stream/$id/$name', 'App\Controller\CDN@stream');
    // $router->get('/download/$id', 'App\Controller\CDN@download');
    $router->get('/download/$id/$name', 'App\Controller\CDN@download');
} else {
    $router->post('/cdn/upload', 'App\Controller\CDN@upload');
    // $router->get('/cdn/stream/$id', 'App\Controller\CDN@stream');
    $router->get('/cdn/stream/$id/$name', 'App\Controller\CDN@stream');
    // $router->get('/cdn/download/$id', 'App\Controller\CDN@download');
    $router->get('/cdn/download/$id/$name', 'App\Controller\CDN@download');
}



$router->run();
