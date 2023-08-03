<?php

namespace App\Controller;

use App\Plugin\Vite;
use eru123\router\Context;

class Main extends Controller
{
    public function view()
    {
        $vite = Vite::instance();
        $vite->setDist(__CLIENT__ . '/main/dist');
        $vite->setAppId('app');
        $vite->useTemplate('vite');
        $vite->header('<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">');
        $vite->header('<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">');
        $vite->header('<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">');
        $vite->header('<link rel="manifest" href="/site.webmanifest">');
        $vite->header('<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">');
        $vite->header('<meta name="msapplication-TileColor" content="#2b5797">');
        $vite->header('<meta name="theme-color" content="#ffffff">');
        return $vite->render();
    }

    public function index(Context $c)
    {
        if ($c->file_path) {
            return null;
        }

        $vite = Vite::instance();
        $vite->data([
            'app_title' => env('APP_TITLE', 'App'),
        ]);

        return $this->view();
    }
}
