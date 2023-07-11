<?php

use eru123\router\Router;

$router = new Router();

$router->get('/', function () {
    Router::status_page(200, 'Welcome!', 'Hi there! Thanks for visiting, the website is currently under construction, please come back later.');
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
