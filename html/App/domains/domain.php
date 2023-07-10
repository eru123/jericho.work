<?php

use eru123\router\Router;

$router = new Router();

$router->get('/', function () {
    Router::status_page(200, 'Hi there!', 'The domain is working! You can start building your website now.');
});

$router->get('/info', function () {
    phpinfo();
    exit;
});

$public_dir = realpath(__DIR__ . '/../../public');
if ($public_dir && is_dir($public_dir)) {
    $router->static('/', [__DIR__ . '/../../public'], ['index.php', 'index.html']);
}

return $router;
