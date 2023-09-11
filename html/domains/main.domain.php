<?php

use eru123\router\Router;

$router = new Router();

$router->bootstrap(function() {
    $contacts = [
        'Admin Mail' => env('ADMIN_MAIL'),
        'Support Mail' => env('SUPPORT_MAIL'),
        'Developer Mail' => env('DEV_MAIL'),
    ];

    foreach ($contacts as $name => $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address for {$name}. Please contact the administrator.", 500);
        }
    }
});

$public_dir = realpath(__CWD__ . '/public');
if ($public_dir && is_dir($public_dir)) {
    $router->static('/', [$public_dir], ['index.php', 'index.html']);
}

$main = require __ROUTES__  . '/main.php';
$cdn = require __ROUTES__  . '/cdn.php';
// $admin = require __ROUTES__  . '/admin.php';
$api = require __ROUTES__  . '/api.php';

$main($router);
$router->child($cdn);
// $router->child($admin);
$router->child($api);

return $router;
