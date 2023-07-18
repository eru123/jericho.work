<?php

namespace App\Plugin;

use eru123\email\provider\SMTP;
use eru123\helper\Format;

class Mail
{
    static $instance = null;
    private $smtp;
    private $template = null;
    private $templateData = [];
    private $avatar = null;

    public static function instance(bool $debug = false)
    {
        if (static::$instance === null) {
            static::$instance = new static($debug);
        }
        return static::$instance;
    }

    public function __construct(bool $debug = false)
    {
        $this->smtp = new SMTP([
            'host' => env('SMTP_HOST'),
            'port' => 587,
            'secure' => 'tls',
            'auth' => true,
            'debug' => $debug,
            'username' => env('SMTP_USER'),
            'password' => env('SMTP_PASS')
        ]);

        $this->smtp->fromName(env('SMTP_FROM_NAME'));
        $this->smtp->fromEmail(env('SMTP_FROM_EMAIL'));
    }

    public function smtp()
    {
        return $this->smtp;
    }

    public function to(string ...$emails)
    {
        foreach ($emails as $email) {
            $this->smtp->to($email);
        }

        return $this;
    }

    public function subject(string $subject)
    {
        $this->smtp->subject($subject);
        return $this;
    }

    public function body(string $body)
    {
        $this->smtp->body($body);
        return $this;
    }

    public function message(string $message)
    {
        $this->smtp->body($message);
        return $this;
    }

    public function avatar($path)
    {
        $path = realpath($path);
        if (file_exists($path)) {
            $this->avatar = file_get_contents($path);
        }
    }

    public function avatarFromUrl(string $url)
    {
        $this->avatar = file_get_contents($url);
    }

    public function send()
    {
        if (!is_null($this->template)) {
            $msg = Format::template($this->template, $this->templateData, FORMAT_TEMPLATE_DOLLAR_CURLY);
            if (!is_null($this->avatar)) {
                $this->smtp->attachment($this->avatar, 'avatar.png', 'image/png');
            }
            $msg .= "<img src=\"cid:avatar\" />";
            $this->smtp->body($msg);
        }

        return $this->smtp->send();
    }

    public function template(string $template, ?array $data = null)
    {
        $this->template = $template;
        if (is_array($data)) {
            $this->templateData = array_merge($this->templateData, $data);
        }

        return $this;
    }

    public function data(array $data)
    {
        $this->templateData = $data;
        return $this;
    }

    public static function mail(string $to, string $subject, string $message, bool $debug = false)
    {
        $smtp = new static($debug);
        $smtp->to($to);
        $smtp->subject($subject);
        $smtp->body($message);
        return $smtp->send();
    }
}
