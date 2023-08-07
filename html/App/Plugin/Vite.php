<?php

namespace App\Plugin;

use eru123\router\Vite as RouterVite;

class Vite extends RouterVite
{
    public static function seo(array $data)
    {
        if (isset($data['url'])) {
            static::head('<meta property="og:url" content="' . $data['url'] . '">');
        }

        if (isset($data['type'])) {
            static::head('<meta property="og:type" content="' . $data['type'] . '">');
            static::head('<meta name="twitter:card" content="' . $data['type'] . '">');
        }

        if (isset($data['title'])) {
            static::head('<meta property="og:title" content="' . $data['title'] . '">');
            static::head('<meta name="title" content="' . $data['title'] . '">');
        }

        if (isset($data['description'])) {
            static::head('<meta property="og:description" content="' . $data['description'] . '">');
            static::head('<meta name="description" content="' . $data['description'] . '">');
        }

        if (isset($data['image'])) {
            static::head('<meta property="og:image" content="' . $data['image'] . '">');
            static::head('<meta name="image" content="' . $data['image'] . '">');
        }

        if (isset($data['app_id'])) {
            static::head('<meta property="fb:app_id" content="' . $data['app_id'] . '">');
        }

        if (isset($data['locale'])) {
            static::head('<meta property="og:locale" content="' . $data['locale'] . '">');
            static::head('<meta name="locale" content="' . $data['locale'] . '">');
        }

        if (isset($data['keywords'])) {
            static::head('<meta name="keywords" content="' . (is_array($data['keywords']) ? implode(',', $data['keywords']) : '') . '">');
        }

        if (isset($data['author'])) {
            static::head('<meta name="author" content="' . $data['author'] . '">');
        }

        if (isset($data['publisher'])) {
            static::head('<meta name="publisher" content="' . $data['publisher'] . '">');
        }

        if (isset($data['robots'])) {
            static::head('<meta name="robots" content="' . $data['robots'] . '">');
        }

        if (isset($data['canonical'])) {
            static::head('<link rel="canonical" href="' . $data['canonical'] . '">');
        }

        if (isset($data['prev'])) {
            static::head('<link rel="prev" href="' . $data['prev'] . '">');
        }

        if (isset($data['next'])) {
            static::head('<link rel="next" href="' . $data['next'] . '">');
        }

        if (isset($data['alternate'])) {
            static::head('<link rel="alternate" href="' . $data['alternate'] . '">');
        }

        if (isset($data['amphtml'])) {
            static::head('<link rel="amphtml" href="' . $data['amphtml'] . '">');
        }

        if (isset($data['manifest'])) {
            static::head('<link rel="manifest" href="' . $data['manifest'] . '">');
        }

        if (isset($data['mask-icon'])) {
            static::head('<link rel="mask-icon" href="' . $data['mask-icon'] . '">');
        }

        if (isset($data['theme-color'])) {
            static::head('<meta name="theme-color" content="' . $data['theme-color'] . '">');
        }

        if (isset($data['apple-mobile-web-app-capable'])) {
            static::head('<meta name="apple-mobile-web-app-capable" content="' . $data['apple-mobile-web-app-capable'] . '">');
        }

        if (isset($data['apple-mobile-web-app-status-bar-style'])) {
            static::head('<meta name="apple-mobile-web-app-status-bar-style" content="' . $data['apple-mobile-web-app-status-bar-style'] . '">');
        }

        if (isset($data['apple-mobile-web-app-title'])) {
            static::head('<meta name="apple-mobile-web-app-title" content="' . $data['apple-mobile-web-app-title'] . '">');
        }

        if (isset($data['msapplication-TileColor'])) {
            static::head('<meta name="msapplication-TileColor" content="' . $data['msapplication-TileColor'] . '">');
        }

        if (isset($data['msapplication-TileImage'])) {
            static::head('<meta name="msapplication-TileImage" content="' . $data['msapplication-TileImage'] . '">');
        }

        if (isset($data['msapplication-config'])) {
            static::head('<meta name="msapplication-config" content="' . $data['msapplication-config'] . '">');
        }

        if (isset($data['application-name'])) {
            static::head('<meta name="application-name" content="' . $data['application-name'] . '">');
        }

        if (isset($data['full-screen'])) {
            static::head('<meta name="full-screen" content="' . $data['full-screen'] . '">');
        }

        if (isset($data['browser-mode'])) {
            static::head('<meta name="browser-mode" content="' . $data['browser-mode'] . '">');
        }

        if (isset($data['night-mode'])) {
            static::head('<meta name="night-mode" content="' . $data['night-mode'] . '">');
        }

        if (isset($data['layout-mode'])) {
            static::head('<meta name="layout-mode" content="' . $data['layout-mode'] . '">');
        }

        if (isset($data['screen-orientation'])) {
            static::head('<meta name="screen-orientation" content="' . $data['screen-orientation'] . '">');
        }

        if (isset($data['color-scheme'])) {
            static::head('<meta name="color-scheme" content="' . $data['color-scheme'] . '">');
        }

        if (isset($data['viewport-fit'])) {
            static::head('<meta name="viewport-fit" content="' . $data['viewport-fit'] . '">');
        }

        if (isset($data['google-site-verification'])) {
            static::head('<meta name="google-site-verification" content="' . $data['google-site-verification'] . '">');
        }

        if (isset($data['yandex-verification'])) {
            static::head('<meta name="yandex-verification" content="' . $data['yandex-verification'] . '">');
        }

        if (isset($data['msvalidate.01'])) {
            static::head('<meta name="msvalidate.01" content="' . $data['msvalidate.01'] . '">');
        }

        if (isset($data['alexaVerifyID'])) {
            static::head('<meta name="alexaVerifyID" content="' . $data['alexaVerifyID'] . '">');
        }

        if (isset($data['p:domain_verify'])) {
            static::head('<meta name="p:domain_verify" content="' . $data['p:domain_verify'] . '">');
        }

        if (isset($data['norton-safeweb-site-verification'])) {
            static::head('<meta name="norton-safeweb-site-verification" content="' . $data['norton-safeweb-site-verification'] . '">');
        }

        if (isset($data['csrf-token'])) {
            static::head('<meta name="csrf-token" content="' . $data['csrf-token'] . '">');
        }

        if (isset($data['csrf-param'])) {
            static::head('<meta name="csrf-param" content="' . $data['csrf-param'] . '">');
        }

        if (isset($data['referrer'])) {
            static::head('<meta name="referrer" content="' . $data['referrer'] . '">');
        }
    }

    public static function data(array $data)
    {
        Vite::body('<script type="module">window.__SERVER_DATA__ = {...(window?.__SERVER_DATA__ || {}), ...' . json_encode($data) . '};</script>');
    }
}
