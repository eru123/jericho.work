<?php

use eru123\router\Router;

$admin = new Router();
$admin->base('/admin');
$admin->static('/', [__DIR__ . '/../../client/admin/dist/admin'], [], 'App\Controller\Admin::index');

return $admin;