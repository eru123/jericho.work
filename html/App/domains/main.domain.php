<?php

use eru123\router\Router;

$router = new Router();

// $router->get('/info', function () {
    // phpinfo();
    // exit;
// });

$router->get('/', function () {
    Router::status_page(200, 'Welcome!', 'Hi there! Thanks for visiting, the website is currently under construction, please come back later.');
});

$public_dir = realpath(__DIR__ . '/../../public');
if ($public_dir && is_dir($public_dir)) {
    $router->static('/', [$public_dir], ['index.php', 'index.html']);
}


$cdn = require __APP__  . '/routes/cdn.php';

$router->child($cdn);
return $router;
