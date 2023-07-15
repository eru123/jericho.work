<?php

namespace App\Plugin;

class CF
{
    static $instance = null;

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function dev_mode($on = null)
    {
        if (is_null($on)) {
            return static::get_dev_mode();
        }

        return static::update_dev_mode($on);
    }

    private static function get_dev_mode()
    {
        $url = 'https://api.cloudflare.com/client/v4/zones/' . env('CF_ZONE_ID') . '/settings/development_mode';
        $headers = [
            'Content-Type: application/json',
            'X-Auth-Email: ' . env('CF_EMAIL'),
            'Authorization: Bearer ' . env('CF_CACHE_TOKEN'),
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($result, true);

        if ($res['success']) {
            return $res['result']['value'] === 'on';
        }

        return false;
    }

    private static function update_dev_mode($on = true)
    {
        $url = 'https://api.cloudflare.com/client/v4/zones/' . env('CF_ZONE_ID') . '/settings/development_mode';
        $data = json_encode([
            'value' => $on ? 'on' : 'off',
        ]);
        $headers = [
            'Content-Type: application/json',
            'X-Auth-Email: ' . env('CF_EMAIL'),
            'Authorization: Bearer ' . env('CF_CACHE_TOKEN'),
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($result, true);
        if ($res['success']) {
            return $res['result']['value'] === 'on';
        }

        return false;
    }

    public static function purge_cache()
    {
        $url = 'https://api.cloudflare.com/client/v4/zones/' . env('CF_ZONE_ID') . '/purge_cache';
        $headers = [
            'Content-Type: application/json',
            'X-Auth-Email: ' . env('CF_EMAIL'),
            'Authorization: Bearer ' . env('CF_CACHE_TOKEN'),
        ];
        $data = json_encode([
            'purge_everything' => true,
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($result, true);
        return isset($res['success']) && $res['success'] === true;
    }
}
