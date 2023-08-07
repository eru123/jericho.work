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

        Vite::dist(__CLIENT__ . '/admin/dist/admin');
        Vite::manifest(__CLIENT__ . '/admin/dist/manifest.json');
        Vite::template('vite');
        Vite::set('base_uri', '');
        Vite::head('<link rel="icon" href="/admin/favicon.ico">');
        Vite::seo([
            'title' => 'Admin'
        ]);

        return Vite::render([], true);
    }
}
