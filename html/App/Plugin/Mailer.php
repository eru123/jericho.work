<?php

namespace App\Plugin;

use eru123\email\provider\SMTP;
use Exception;

class Mailer
{

    protected static $pool = [];
    private $pool_key = null;

    public function __construct(string $key = 'default', SMTP|array $config = null, bool $debug = false)
    {
        $this->pool_key = $key;
        if (isset(static::$pool[$key])) {
            return;
        }

        if (is_array($config)) {
            static::$pool[$key] = new SMTP($config);
        } elseif ($config instanceof SMTP) {
            static::$pool[$key] = $config;
        } else {
            $this->pool['key'] = new SMTP([
                'host' => env('SMTP_HOST'),
                'port' => 587,
                'secure' => 'tls',
                'auth' => true,
                'debug' => $debug,
                'username' => env('SMTP_USER'),
                'password' => env('SMTP_PASS'),
                'from_name' => env('SMTP_FROM_NAME'),
                'from_email' => env('SMTP_FROM_EMAIL'),
            ]);
        }
    }

    public function delete()
    {
        unset(static::$pool[$this->pool_key]);
    }

    public static function instance(string $key = 'default', SMTP|array $config = null, bool $debug = false)
    {
        return new static($key, $config, $debug);
    }

    public static function queue(array $data = [])
    {
        $required = ['to', 'subject', 'body'];
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                throw new Exception("Required key {$key} not found");
            }
        }

        if (!is_array($data['to'])) {
            $data['to'] = [$data['to']];
        }

        if (isset($data['cc']) && !is_array($data['cc'])) {
            $data['cc'] = [$data['cc']];
        }

        if (isset($data['bcc']) && !is_array($data['bcc'])) {
            $data['bcc'] = [$data['bcc']];
        }

        $to = [];
        foreach ($data['to'] as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $to[] = $email;
            }
        }

        $cc = [];
        if (isset($data['cc'])) {
            foreach ($data['cc'] as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $cc[] = $email;
                }
            }
        }

        $bcc = [];
        if (isset($data['bcc'])) {
            foreach ($data['bcc'] as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $bcc[] = $email;
                }
            }
        }

        if (count($to + $cc + $bcc) < 1) {
            throw new Exception("No valid email address found");
        }

        $maildata = [
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $data['subject'],
            'body' => $data['body'],
        ];
    }
}
