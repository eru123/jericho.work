<?php

use eru123\router\Router;

return function (Router &$main) {
    $main->fallback('App\Controller\Main@index');
    $main->static('/', [__CLIENT__ . '/main/dist'], []);
    $main->get('/', 'App\Controller\Main@index');
};
