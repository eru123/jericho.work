<?php

namespace eru123\http;

use InvalidArgumentException;

class Fetch
{
    public static function httpForwardedTo($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid proxy URL', 400);
        }

        $protocol = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($protocol, ['http', 'https'])) {
            throw new InvalidArgumentException('Invalid Protocol', 400);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $_COOKIE));

        $headers = [];
        foreach (getallheaders() as $key => $value) {
            if (in_array(strtolower($key), ['host', 'content-length', 'content-type'])) {
                continue;
            }
            $headers[] = $key . ': ' . $value;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
        }

        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $headers = explode("\r\n", $header);
        foreach ($headers as $header) {
            if (empty($header)) {
                continue;
            }
            header($header);
        }

        return $body;
    }

    public static function text(string $url, $data = null, string $method = 'GET', array $headers = [])
    {
        // emulating fetch function in javascript that returns a response as string
        $method = trim(strtoupper($method));

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL', 400);
        }

        $has_data = !empty($data) && is_array($data);

        if ($method === 'GET' && !empty($data) && is_array($data)) {
            $qm = strpos($url, '?');
            if ($qm !== false && $qm < strlen($url) - 1) {
                $url .= '&';
            } else if ($qm === false) {
                $url .= '?';
            }

            if (!empty($data)) {
                $url .= http_build_query($data);
            }
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, empty($method) ? 'GET' : $method);

        if (!empty($data) && $method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        }
    }
}
