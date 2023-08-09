<?php

use eru123\router\Router;
use App\Plugin\Vite;

return function (Router &$main) {
    Vite::src(__CLIENT__ . '/main/src');
    Vite::public(__CLIENT__ . '/main/public');
    Vite::dist(__CLIENT__ . '/main/dist');
    Vite::manifest(__CLIENT__ . '/main/dist/manifest.json');
    Vite::template(env('APP_ENV', 'production') === 'production' ? 'vite' : 'dev');
    Vite::set('base_uri', env('APP_ENV', 'production') === 'production' ? '' : 'http://localhost:3000');
    Vite::inject($main);

    $main->fallback('App\Controller\Main@index');
    $main->any('/site.webmanifest', 'App\Controller\Main@webmanifest');
    $main->get('/', 'App\Controller\Main@index');
};
