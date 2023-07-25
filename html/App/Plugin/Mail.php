<?php

namespace App\Plugin;

use eru123\email\provider\SMTP;
use eru123\helper\Format;

class Mail
{
    static $instance = null;
    private $smtp;
    private $copy = null;
    private $template = null;
    private $templateData = [];
    private $queue = false;
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $priority = 0;
    private $subject = '';
    private $body = ''; 
    private $user_id = 0;
    private $parent_id = 0;

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

        $this->copy = clone $this->smtp;
    }

    public function enableQueue()
    {
        $this->queue = true;
        return $this;
    }

    public function disableQueue()
    {
        $this->queue = false;
        return $this;
    }

    public function smtp()
    {
        return $this->smtp;
    }

    public function priority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }


    public function to(string ...$emails)
    {
        $this->to = $emails + $this->to;
        return $this;
    }

    public function cc(string ...$emails)
    {
        $this->cc = $emails + $this->cc;
        return $this;
    }

    public function bcc(string ...$emails)
    {
        $this->bcc = $emails + $this->bcc;
        return $this;
    }

    public function subject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string $body)
    {
        $this->body = $body;
        return $this;
    }

    public function message(string $message)
    {
        $this->body = $message;
        return $this;
    }

    public function send()
    {
        if (!is_null($this->template)) {
            $msg = Format::template($this->template, $this->templateData, FORMAT_TEMPLATE_DOLLAR_CURLY);
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

    public function queue($data)
    {
        $db = DB::instance();
        $db->insert('mail_queue', $data);
    }
}
