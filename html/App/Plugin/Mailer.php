<?php

namespace App\Plugin;

use Exception;
use eru123\email\provider\SMTP;
use App\Models\Mails as MailModel;
use App\Models\Smtps as SmtpsModel;
use App\Plugin\MC;

class Mailer
{
    const CACHE_PREFIX = 'mails_';
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
            static::$pool[$key] = new SMTP([
                'host' => env('SMTP_HOST'),
                'port' => env('SMTP_PORT', 587),
                'secure' => env('SMTP_SECURE', 'tls'),
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

    public static function create_mail_data(array $data = []): array
    {
        $required = ['to', 'subject', 'body'];
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                throw new Exception("Required key '{$key}' not found");
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

        $priority = @$data['priority'] ?: 1;
        $type = @$data['type'] ?: 'transactional';
        $status = MailModel::STATUS_QUEUE;
        $sender_id = @$data['sender_id'] ?: 0;
        $parent_id = @$data['parent_id'] ?: 0;
        $user_id = @$data['user_id'] ?: 0;
        $attachments = @$data['attachments'] ?: [];

        if (!is_array($attachments)) {
            if (is_numeric($attachments)) {
                $attachments = [intval($attachments)];
            } else {
                throw new Exception("Invalid attachment: " . $attachments);
            }
        }

        $invalid_attachments = [];
        foreach ($attachments as $key => $value) {
            if (!is_numeric($value)) {
                $invalid_attachments[] = $value;
            } else if (!is_int($value) && !is_float($value)) {
                $attachments[$key] = intval($value);
            }
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], [MailModel::STATUS_QUEUE, MailModel::STATUS_SENT, MailModel::STATUS_FAILED])) {
                throw new Exception("Invalid mail status");
            }

            $status = $data['status'];
        }

        $meta = $data;
        $exclude = ['to', 'cc', 'bcc', 'subject', 'body', 'priority', 'type', 'status', 'sender_id', 'parent_id', 'user_id'];
        foreach ($exclude as $key) {
            unset($meta[$key]);
        }

        return [
            'parent_id' => (int) $parent_id,
            'user_id' => (int) $user_id,
            'sender_id' => (int) $sender_id,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'priority' => (int) $priority,
            'type' => $type,
            'status' => (int) $status,
            'meta' => $meta,
        ];
    }

    public static function queue(array $data = [])
    {
        $maildata = static::create_mail_data($data);
        $insert = MailModel::insert($maildata);
        if (!$insert->rowCount()) {
            throw new Exception("Failed to insert mail data");
        }

        return true;
    }

    public function send(array $data = [])
    {
        $maildata = static::create_mail_data($data);
        $success = static::$pool[$this->pool_key]->send($maildata);
        $status = $success ? MailModel::STATUS_SENT : MailModel::STATUS_FAILED;
        $maildata['status'] = $status;
        if ($success) {
            $maildata['message_id'] = static::$pool[$this->pool_key]->id();
        }
        $maildata['response'] = [
            'provider' => static::$pool[$this->pool_key]->provider() ?? 'Unknown',
            'logs' => static::$pool[$this->pool_key]->logs()
        ];
        return $maildata;
    }

    public static function send_queues()
    {
        $mails = @MailModel::get_queues()['mails'] ?? [];
        if (count($mails)) {
            foreach ($mails as $mail) {
                cmd(['mail_queue', '-q', $mail['id']], true);
                continue;
            }
        }
        return count($mails);
    }

    public static function new_process()
    {
        $identifier = static::CACHE_PREFIX . 'processing_count';
        $mc = MC::instance();
        $mc->set($identifier, ($mc->get($identifier) ?? 0) + 1, 60);
    }

    public static function end_process()
    {
        $identifier = static::CACHE_PREFIX . 'processing_count';
        $mc = MC::instance();
        $mc->set($identifier, ($mc->get($identifier) ?? 0) - 1, 60);
    }

    public static function total_process()
    {
        $identifier = static::CACHE_PREFIX . 'processing_count';
        $mc = MC::instance();
        $cnt = $mc->get($identifier) ?? 0;
        return $cnt < 0 ? 0 : $cnt;
    }
}
