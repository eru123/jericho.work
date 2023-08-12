<?php

declare(strict_types=1);

namespace eru123\email\provider;

use Exception;

class SMTP implements OutboundInterface
{
    private $config = [];
    private $last_transaction_id = null;
    private $last_transaction_provider = null;
    private $response = [];
    private $fullresponse = [];

    public function __construct(array $config = [])
    {
        $this->build_config($config);
        if ($this->config['secure'] && !in_array($this->config['secure'], ['ssl', 'tls'])) {
            throw new Exception('Invalid secure type: ' . $this->config['secure']);
        }
    }

    private function build_config(array $config = []): array
    {
        $cfg = array_merge([
            'host' => 'localhost',
            'port' => 587,
            'timeout' => 30,
            'auth' => true,
            'username' => '',
            'password' => '',
            'secure' => false,
            'debug' => false,
            'ssl' => false,
            'time' => time(),
            'eol' => "\r\n",
        ], $this->config ?? [], $config);

        if ($cfg['secure'] == 'ssl') {
            $cfg['ssl'] = array_merge([
                'verify_peer' => false,
                'verify_depth' => 3,
                'allow_self_signed' => true,
                'peer_name' => $cfg['host'],
                'cafile' => realpath(__DIR__ . '/../cert/cacert.pem'),
            ], (is_array($cfg['ssl']) ? $cfg['ssl'] : []));
        }

        if ($cfg['ssl'] && !isset($cfg['ssl']['peer_name'])) {
            $cfg['ssl']['peer_name'] = $cfg['host'];
        }

        $this->config = $cfg;
        return $cfg;
    }

    private function debug($message): void
    {
        if (!$this->config['debug']) {
            return;
        }

        if (in_array(PHP_SAPI, ['cli', 'phpdbg'])) {
            $use_color = substr($message, 0, 3) == '<< ' || substr($message, 0, 3) == '>> ';
            $recv_color = "\033[36m";
            $send_color = "\033[32m";

            if ($use_color) {
                if (substr($message, 0, 2) == '<<') {
                    $message = $recv_color . $message . "\033[0m";
                } elseif (substr($message, 0, 2) == '>>') {
                    $message = $send_color . $message . "\033[0m";
                }
            }

            echo $message;
            if (!in_array(substr($message, -1), ["\r", "\n"])) {
                echo PHP_EOL;
            }
        } else {
            echo str_replace(PHP_EOL, '<br>', $message);
            if (!in_array(substr($message, -1), ["\r", "\n"])) {
                echo '<br>';
            }
        }
    }

    public function eol(string $eol): static
    {
        $this->config['eol'] = $eol;
        return $this;
    }

    public function break(string $break): static
    {
        $this->config['eol'] = $break;
        return $this;
    }

    public function br(string $break): static
    {
        $this->config['eol'] = $break;
        return $this;
    }

    public function connect(array $config = [], bool $debug = false)
    {
        $cfg = $this->build_config($config);
        !$debug || $this->debug('== Connecting to ' . $cfg['host'] . ':' . $cfg['port']);

        $socket = null;

        if ($cfg['ssl']) {
            !$debug || $this->debug('== Using SSL');
            if (!extension_loaded('openssl')) {
                throw new Exception('SSL extension not loaded');
            }

            $context = stream_context_create([
                'ssl' => $cfg['ssl']
            ]);

            $socket = stream_socket_client('ssl://' . $cfg['host'] . ':' . $cfg['port'], $errno, $errstr, $cfg['timeout'], STREAM_CLIENT_CONNECT, $context);
        } else {
            $socket = fsockopen($cfg['host'], $cfg['port'], $errno, $errstr, $cfg['timeout']);
        }

        if (!$socket) {
            throw new Exception($errstr, $errno);
        }

        return $socket;
    }

    public function from(string $name, string $email): static
    {
        $this->config['from_name'] = $name;
        $this->config['from_email'] = $email;
        return $this;
    }

    public function from_name(string $name): static
    {
        $this->config['from_name'] = $name;
        return $this;
    }

    public function from_email(string $email): static
    {
        $this->config['from_email'] = $email;
        return $this;
    }

    public function reply_to(string $email): static
    {
        if (!isset($this->config['reply_to'])) {
            $this->config['reply_to'] = [];
        }

        if (isset($this->config['reply_to']) && empty($this->config['reply_to'])) {
            $this->config['reply_to'] = [];
        }

        $this->config['reply_to'][] = $email;
        return $this;
    }

    public function to(string $email): static
    {
        if (!isset($this->config['to'])) {
            $this->config['to'] = [];
        }

        if (isset($this->config['to']) && empty($this->config['to'])) {
            $this->config['to'] = [];
        }

        $this->config['to'][] = $email;
        return $this;
    }

    public function cc(string $email): static
    {
        if (!isset($this->config['cc'])) {
            $this->config['cc'] = [];
        }

        if (isset($this->config['cc']) && empty($this->config['cc'])) {
            $this->config['cc'] = [];
        }

        $this->config['cc'][] = $email;
        return $this;
    }

    public function bcc(string $email): static
    {
        if (!isset($this->config['bcc'])) {
            $this->config['bcc'] = [];
        }

        if (isset($this->config['bcc']) && empty($this->config['bcc'])) {
            $this->config['bcc'] = [];
        }

        $this->config['bcc'][] = $email;
        return $this;
    }

    public function add_to(string $email): static
    {
        return $this->to($email);
    }

    public function add_cc(string $email): static
    {
        return $this->cc($email);
    }

    public function add_bcc(string $email): static
    {
        return $this->bcc($email);
    }

    public function subject(string $subject): static
    {
        $this->config['subject'] = $subject;
        return $this;
    }

    public function body(string $body): static
    {
        $this->config['body'] = $body;
        return $this;
    }

    public function auth(string $username, string $password): static
    {
        $this->config['username'] = $username;
        $this->config['password'] = $password;
        $this->config['auth'] = true;
        return $this;
    }

    public function use_auth(bool $auth = true): static
    {
        $this->config['auth'] = $auth;
        return $this;
    }

    public function use_ssl(array $context = []): static
    {
        $this->config['ssl'] = empty($context) ? $this->config['ssl'] : $context;
        $this->config['secure'] = 'ssl';
        return $this;
    }

    public function use_tls(): static
    {
        $this->config['secure'] = 'tls';
        return $this;
    }

    public function use_unsecure(): static
    {
        $this->config['secure'] = false;
        return $this;
    }

    public function secure(string $secure): static
    {
        $this->config['secure'] = $secure;
        return $this;
    }

    public function enable_debug(): static
    {
        $this->config['debug'] = true;
        return $this;
    }

    public function disable_debug(): static
    {
        $this->config['debug'] = false;
        return $this;
    }

    public function use_debug(bool $debug = true): static
    {
        $this->config['debug'] = $debug;
        return $this;
    }

    public function timeout(int $seconds): static
    {
        $this->config['timeout'] = $seconds;
        return $this;
    }

    public function use_timeout(int $seconds): static
    {
        $this->config['timeout'] = $seconds;
        return $this;
    }

    public function use_port(int $port): static
    {
        $this->config['port'] = $port;
        return $this;
    }

    public function port(int $port): static
    {
        $this->config['port'] = $port;
        return $this;
    }

    public function use_host(string $host): static
    {
        $this->config['host'] = $host;
        return $this;
    }

    public function host(string $host): static
    {
        $this->config['host'] = $host;
        return $this;
    }

    public function use_username(string $username): static
    {
        $this->config['username'] = $username;
        return $this;
    }

    public function username(string $username): static
    {
        $this->config['username'] = $username;
        return $this;
    }

    public function use_password(string $password): static
    {
        $this->config['password'] = $password;
        return $this;
    }

    public function password(string $password): static
    {
        $this->config['password'] = $password;
        return $this;
    }

    public function use_time(int $time): static
    {
        $this->config['time'] = $time;
        return $this;
    }

    public function time(int $time): static
    {
        $this->config['time'] = $time;
        return $this;
    }

    public function send(array $data = []): bool
    {
        $socket = null;
        $this->last_transaction_id = null;
        $this->last_transaction_provider = null;
        $this->response = [];
        $this->fullresponse = [];

        try {
            $data = array_merge([
                'from_email' => '',
                'from_name' => '',
                'reply_to' => false,
                'to' => [],
                'cc' => [],
                'bcc' => [],
                'subject' => '',
                'body' => '',
                'attachments' => [],
            ], $this->config, $data);

            if (!empty($data['to']) && is_string($data['to'])) {
                $data['to'] = count(explode(',', $data['to'])) > 1 ? explode(',', $data['to']) : $data['to'];
            } else if (empty($data['to'])) {
                $data['to'] = [];
            }

            if (!empty($data['cc']) && is_string($data['cc'])) {
                $data['cc'][] = count(explode(',', $data['cc'])) > 1 ? explode(',', $data['cc']) : $data['cc'];
            } else if (empty($data['cc'])) {
                $data['cc'] = [];
            }

            if (!empty($data['bcc']) && is_string($data['bcc'])) {
                $data['bcc'][] = count(explode(',', $data['bcc'])) > 1 ? explode(',', $data['bcc']) : $data['bcc'];
            } else if (empty($data['bcc'])) {
                $data['bcc'] = [];
            }

            $recipients = array_merge($data['to'], $data['cc'], $data['bcc']);
            if (empty($recipients)) {
                throw new Exception('No recipients');
            }

            $socket = $this->connect([], $this->config['debug']);

            $this->debug('== Checking connection');
            $this->read($socket);

            $this->debug('== Connected, sending EHLO');
            $this->write($socket, 'EHLO ' . $this->config['host']);
            $ehlo = $this->read($socket);

            if (strpos($ehlo, '250') !== 0) {
                $this->debug('== EHLO not supported, sending HELO');
                $this->write($socket, 'HELO ' . $this->config['host']);
                $this->read($socket);
            }

            if ($this->config['secure'] == 'tls') {
                $this->debug('== Starting TLS');
                $this->write($socket, 'STARTTLS');
                $this->read($socket);

                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new Exception('Unable to start TLS');
                }
            }

            if ($this->config['auth']) {
                $this->write($socket, 'AUTH LOGIN');
                $this->read($socket);

                $this->write($socket, base64_encode($this->config['username']));
                $this->read($socket);

                $this->write($socket, base64_encode($this->config['password']));
                $this->read($socket);
            }

            $this->write($socket, 'MAIL FROM: <' . $data['from_email'] . '>');
            $this->read($socket);

            foreach ($recipients as $recipient) {
                $this->write($socket, 'RCPT TO: <' . $recipient . '>');
                $this->read($socket);
            }

            $this->write($socket, 'DATA');
            $this->read($socket);

            if (empty($data['from_email']) && !empty($data['username'])) {
                $data['from_email'] = $data['username'];
            }

            if (empty($data['from_name'])) {
                $data['from_name'] = $data['from_email'];
            }

            if (!empty($data['from_email'])) {
                $this->write($socket, 'From: ' . $data['from_name'] . ' <' . $data['from_email'] . '>');
            }

            if (!empty($data['to'])) {
                $this->write($socket, 'To: ' . implode(', ', $data['to']));
            }

            if (!empty($data['reply_to'])) {
                $this->write($socket, 'Reply-To: ' . $data['reply_to']);
            }

            if (!empty($data['cc'])) {
                $this->write($socket, 'Cc: ' . implode(', ', $data['cc']));
            }

            $hash = md5((string) $this->config['time']);

            $this->write($socket, 'Subject: ' . $data['subject']);
            $this->write($socket, 'MIME-Version: 1.0');
            $this->write($socket, 'Content-Type: multipart/mixed; boundary="=_NextPart_' . $hash . '"');
            $this->write($socket, '');
            $this->write($socket, '--=_NextPart_' . $hash);
            $this->write($socket, 'Content-Type: text/html; charset="utf-8"');
            $this->write($socket, 'Content-Transfer-Encoding: 8bit');
            $this->write($socket, '');
            $this->write($socket, $data['body']);
            $this->write($socket, '');

            foreach ($data['attachments'] as $attachment) {
                $this->write($socket, '--=_NextPart_' . $hash);
                $this->write($socket, 'Content-Type: ' . $attachment['type'] . '; name="' . $attachment['name'] . '"');
                $this->write($socket, 'Content-Transfer-Encoding: base64');
                $this->write($socket, 'Content-Disposition: attachment; filename="' . $attachment['name'] . '"');
                $this->write($socket, '');
                $this->write($socket, chunk_split(base64_encode($attachment['content'])));
                $this->write($socket, '');
            }

            $this->write($socket, '--=_NextPart_' . $hash . '--');
            $this->write($socket, '.');

            $datr = $this->read($socket);
            $this->set_last_transaction_id($datr);

            $this->write($socket, 'QUIT');
            $this->read($socket);

            if (is_resource($socket)) {
                fclose($socket);
            }
            return true;
        } catch (Exception $e) {
            if (is_resource($socket)) {
                fclose($socket);
            }
            return false;
        }
    }

    private function write($socket, $data): void
    {
        $this->debug(">> " . substr($data, 0, 64) . (strlen($data) > 64 ? '...' : ''));
        fwrite($socket, $data . (@$this->config['eol'] ?? "\r\n"));
        $this->fullresponse[] = $data;
    }

    private function read($socket): string
    {
        $data = '';
        while ($str = fgets($socket, 512)) {
            $data .= $str;
            if (substr($str, 0, 1) == '4' || substr($str, 0, 1) == '5') {
                throw new Exception($str);
            }
            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }
        $this->debug('<< ' . str_replace(["\r", "\n"], ["\r<< ", "\n<< "], trim($data)));
        $this->response[] = $data;
        return $data;
    }

    private function set_last_transaction_id(string $response): void
    {
        $smtp_transaction_id_patterns = [
            'exim' => '/[\d]{3} OK id=(.*)/',
            'sendmail' => '/[\d]{3} 2.0.0 (.*) Message/',
            'postfix' => '/[\d]{3} 2.0.0 Ok: queued as (.*)/',
            'Microsoft_ESMTP' => '/[0-9]{3} 2.[\d].0 (.*)@(?:.*) Queued mail for delivery/',
            'Amazon_SES' => '/[\d]{3} Ok (.*)/',
            'SendGrid' => '/[\d]{3} Ok: queued as (.*)/',
            'CampaignMonitor' => '/[\d]{3} 2.0.0 OK:([a-zA-Z\d]{48})/',
            'Haraka' => '/[\d]{3} Message Queued \((.*)\)/',
            'ZoneMTA' => '/[\d]{3} Message queued as (.*)/',
            'Mailjet' => '/[\d]{3} OK queued as (.*)/',
            'Gmail' => '/[\d]{3} 2.0.0 (.*) - gsmtp/'
        ];

        foreach ($smtp_transaction_id_patterns as $key => $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $this->last_transaction_id = trim($matches[1]);
                $this->last_transaction_provider = $key;
                break;
            }
        }

        if (empty($this->last_transaction_id)) {
            $this->last_transaction_id = $response;
        }
    }

    public function id(): ?string
    {
        return $this->last_transaction_id;
    }

    public function provider(): ?string
    {
        return $this->last_transaction_provider;
    }

    public function logs(): array
    {
        return $this->response;
    }

    public function full_logs(): array
    {
        return $this->fullresponse;
    }

    public function __destruct()
    {
        $this->config = null;
    }
}
