<?php

namespace App\Controller;

use App\Plugin\Vite;
use eru123\router\Context;

class Main extends Controller
{
    public function index(Context $c)
    {
        if ($c->file_path) {
            return null;
        }

        $vite = Vite::instance();
        $vite->setDist(__CLIENT__ . '/main/dist');
        $vite->setAppId('app');
        $vite->useTemplate('vite');
        $vite->data([
            'app_title' => env('APP_TITLE', 'App'),
        ]);
        $vite->header("<link rel=\"icon\" href=\"/favicon.ico\">");
        return $vite->render();
    }
}
