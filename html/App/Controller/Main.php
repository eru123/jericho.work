<?php

namespace App\Controller;

use App\Plugin\Vite;
use eru123\router\Context;

class Main extends Controller
{
    public function view(array $data = [])
    {
        Vite::head('<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">');
        Vite::head('<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">');
        Vite::head('<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">');
        Vite::head('<link rel="manifest" href="/site.webmanifest">');
        Vite::head('<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">');
        Vite::head('<meta name="msapplication-TileColor" content="#2b5797">');
        Vite::head('<meta name="theme-color" content="#ffffff">');
        return Vite::render($data, true);
    }

    public function index(Context $c)
    {
        if ($c->file_path) {
            return null;
        }

        Vite::seo([
            'title' => env('APP_TITLE', 'App'),
            'description' => "Your dream, your solution, let's make it.",
            'image' => env('BASE_URL') . '/card.png',
            'url' => env('BASE_URL'),
            'type' => 'website'
        ]);

        return $this->view();
    }

    public function webmanifest()
    {
        if (!headers_sent()) {
            http_response_code(200);
            header('Content-Type: application/manifest+json');
        }

        return [
            "name" => env('APP_TITLE', 'App'),
            "short_name" => env('APP_TITLE', 'App'),
            "icons" => [
                [
                    "src" => "/android-chrome-192x192.png",
                    "sizes" => "192x192",
                    "type" => "image/png",
                ],
                [
                    "src" => "/android-chrome-512x512.png",
                    "sizes" => "512x512",
                    "type" => "image/png",
                ],
            ],
            "theme_color" => "#ffffff",
            "background_color" => "#ffffff",
            "display" => "standalone",
        ];
    }
}
