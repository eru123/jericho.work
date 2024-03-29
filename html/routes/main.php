<?php

use eru123\router\Router;
use eru123\router\ViteExeption;
use App\Plugin\Vite;
use App\Controller\Main;

return function (Router &$main) {
    Vite::src(__FE_MAIN__ . '/src');
    Vite::public(__FE_MAIN__ . '/public');
    Vite::dist(__FE_MAIN__ . '/dist');
    Vite::manifest(__FE_MAIN__ . '/dist/manifest.json');
    Vite::template(env('MAIN_VITE_TEMPLATE', 'vite'));
    Vite::set('base_uri', env('MAIN_VITE_BASE_URI', ''));
    Vite::inject($main);

    $main->error(function (Throwable $r) {
        if ($r instanceof ViteExeption) {
            return Router::status_page(500, '500 Internal Server Error', 'Vite: '. trim($r->getMessage(), '"'));
        }

        Vite::data([
            'error' => [
                'code' => $r->getCode(),
                'message' => $r->getMessage(),
                'file' => $r->getFile(),
                'line' => $r->getLine(),
                'trace' => $r->getTrace(),
            ]
        ]);

        return (new Main)->view();
    });

    $main->fallback('App\Controller\Main@index');
    $main->any('/site.webmanifest', 'App\Controller\Main@webmanifest');
    $main->get('/', 'App\Controller\Main@index');

    $main->get('/verify/$code', 'App\Controller\Main@verify');
};
