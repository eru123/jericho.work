<?php

use eru123\router\Router;

return function (Router &$main) {
    $main->fallback('App\Controller\Main@index');
    $main->static('/', [__CLIENT__ . '/main/dist'], []);
    $main->any('/site.webmanifest', 'App\Controller\Main@webmanifest');
    $main->get('/', 'App\Controller\Main@index');
};
