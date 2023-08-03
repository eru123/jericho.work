<?php

use eru123\router\Router;

$router = new Router();

$alternative_links = [
    'OpenCDN' => '/cdn',
];

$public_dir = realpath(__CWD__ . '/public');
if ($public_dir && is_dir($public_dir)) {
    $router->static('/', [$public_dir], ['index.php', 'index.html']);
}

$main = require __ROUTES__  . '/main.php';
$cdn = require __ROUTES__  . '/cdn.php';
$admin = require __ROUTES__  . '/admin.php';
$api = require __ROUTES__  . '/api.php';

$main($router);
$router->child($cdn);
$router->child($admin);
$router->child($api);

return $router;
