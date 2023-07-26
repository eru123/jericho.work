<?php

use eru123\router\Router;

$portfolio = new Router();
$portfolio->static('/', [__DIR__ . '/../../client/admin/dist/admin'], [], 'App\Controller\Admin::index');

return $portfolio;
