<?php

namespace App\Controller;

use App\Plugin\Vite;
use App\Plugin\DB;
use eru123\router\Context;

class Main extends Controller
{
    private function safe_request_uri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('?', $uri)[0];
        $uri = explode('#', $uri)[0];
        return htmlspecialchars($uri);
    }

    public function view(array $data = [])
    {
        Vite::head('<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">');
        Vite::head('<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">');
        Vite::head('<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">');
        Vite::head('<link rel="manifest" href="/site.webmanifest">');
        Vite::head('<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">');
        Vite::head('<meta name="msapplication-TileColor" content="#2b5797">');
        Vite::head('<meta name="theme-color" content="#ffffff">');

        Vite::data([
            'APP_TITLE' => env('APP_TITLE', 'App'),
            'BASE_URL' => env('BASE_URL'),
            'CDN_URL' => env('CDN_URL', env('BASE_URL')),
            'REQUEST_URI' => $this->safe_request_uri(),
            'WS_HOST' => env('WS_HOST', env('BASE_URL') . '/ws'),
        ]);

        if (env('APP_ENV') === 'development') {
            if (!isset($debug['debug'])) {
                $debug['debug'] = [];
            }

            $debug['debug']['response_debug'] = true;
            $debug['debug']['db_query'] = DB::instance()->queryHistory();
            $debug['debug']['memory'] = [
                'usage' => ceil(memory_get_usage() / 1024 / 1024) . 'MB',
                'usage_alloc' => ceil(memory_get_usage(true) / 1024 / 1024) . 'MB',
                'peak' => ceil(memory_get_peak_usage() / 1024 / 1024) . 'MB',
                'peak_alloc' => ceil(memory_get_peak_usage(true) / 1024 / 1024) . 'MB',
            ];

            $debug['debug']['request'] = [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
                'query' => $_GET,
                'body' => (json_decode(file_get_contents('php://input'), true) ?? null) ?: $_POST,
                'headers' => getallheaders(),
            ];

            Vite::data($debug);
        }

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

    public function verify(Context $c)
    {
        if ($c->file_path) {
            return null;
        }

        $code = $c->params['code'] ?? null;

        Vite::seo([
            'title' => "Mail Verification | " . env('APP_TITLE', 'App'),
            'description' => "Mail Verification",
            'image' => env('BASE_URL') . '/card.png',
            'url' => env('BASE_URL'),
            'type' => 'website'
        ]);

        Vite::data(Verification::verify_from_link($code));

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
            'start_url' => '/',
        ];
    }
}
