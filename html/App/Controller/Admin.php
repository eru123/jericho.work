<?php

namespace App\Controller;

use App\Plugin\Vite;
use eru123\router\Context;

class Admin extends Controller
{
    public function index(Context $c)
    {
        if ($c->file_path) {
            return null;
        }

        $vite = Vite::instance();
        $vite->setDist(__DIR__ . '/../../client/admin/dist');
        $vite->setAppId('app');
        $vite->useTemplate('vite');
        $vite->data([
            'app_title' => 'Admin',
        ]);

        return $vite->render('', false);
    }
}
