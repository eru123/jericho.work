<?php

use eru123\router\Router;

$router = new Router();

// $router->get('/info', function () {
// phpinfo();
// exit;
// });

$alternative_links = [
    'OpenCDN' => '/cdn',
];

$router->get('/', function () use ($alternative_links) {
    $msg = 'Hi there! Thanks for visiting,<br /> the website is currently under construction, please come back later. <br /><br />';
    $msg .= 'In the meantime, you can visit the our other services that are working: '. implode(', ', array_map(function ($name, $link) {
        $name = htmlspecialchars($name);
        $link = htmlspecialchars($link);
        return "<a href=\"$link\">$name</a>";
    }, array_keys($alternative_links), $alternative_links));
    Router::status_page(200, 'Welcome!', $msg);
});

$public_dir = realpath(__DIR__ . '/../../public');
if ($public_dir && is_dir($public_dir)) {
    $router->static('/', [$public_dir], ['index.php', 'index.html']);
}


$cdn = require __ROUTES__  . '/cdn.php';
$admin = require __ROUTES__  . '/admin.php';
$api = require __ROUTES__  . '/api.php';

$router->child($cdn);
$router->child($admin);
$router->child($api);
return $router;
