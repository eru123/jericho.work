<?php

use eru123\router\Router;

$router = new Router();

$router->get('/', function () {
    Router::status_page(200, 'Welcome!', 'Hi there! The domain you are visiting is currently not configured, please come back later.');
});

// $router->get('/info', function () {
//     phpinfo();
//     exit;
// });

$public_dir = realpath(__DIR__ . '/../../public');
if ($public_dir && is_dir($public_dir)) {
    $router->static('/', [__DIR__ . '/../../public'], ['index.php', 'index.html']);
}

return $router;
