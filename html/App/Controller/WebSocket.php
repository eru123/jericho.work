<?php

namespace App\Controller;

use Socket;
use Exception;
use eru123\helper\Date;

class WebSocket
{
    private $socket;
    private $clients = [];
    private $users = [];
    private $address;
    private $port;

    public function __construct(string $address, int $port)
    {
        $this->address = $address;
        $this->port = $port;
    }

    function send_message(string|array|object|int $msg)
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = json_encode($msg);
        } else if (is_int($msg)) {
            $msg = (string) $msg;
        }

        $msg = $this->mask($msg);
        $len = strlen($msg);
        $read = $this->clients;
        array_walk($read, function ($sock) use ($msg, $len) {
            @socket_write($sock, $msg, $len);
        });
        return true;
    }

    function send_sock_message(Socket &$sock, string|array|object|int $msg)
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = json_encode($msg);
        } else if (is_int($msg)) {
            $msg = (string) $msg;
        }

        $msg = $this->mask($msg);
        return @socket_write($sock, $msg, strlen($msg));
    }

    function unmask($text)
    {
        if (empty($text)) return "";
        $length = ord($text[1]) & 127;

        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } else if ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }

        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    function mask($text)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if ($length <= 125)
            $header = pack('CC', $b1, $length);
        else if ($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        else if ($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);

        return $header . $text;
    }

    function perform_handshaking($receved_header, $client_conn, $host, $port)
    {
        $headers = [];
        $lines = preg_split("/\r\n/", $receved_header);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $secKey = isset($headers['Sec-WebSocket-Key']) ? $headers['Sec-WebSocket-Key'] : false;
        if ($secKey) {
            $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        }
        // $protocol = 'ws';
        // $secure = isset($headers['Sec-WebSocket-Protocol']);

        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host\r\n" .
            "WebSocket-Location: ws://$host:$port\r\n";
        // "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        if ($secKey) {
            $upgrade .= "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        } else {
            $upgrade .= "\r\n";
        }

        socket_write($client_conn, $upgrade, strlen($upgrade));
    }

    function process_sock_auth(Socket &$sock, array $data)
    {
        if (!isset($data['token'])) {
            return;
        }

        try {
            Date::setTime();
            $token = $data['token'];
            $user = Auth::jwt()->decode($token);
            if (!isset($user['id'])) {
                return;
            }

            $user_id = $user['id'];
            $this->users[spl_object_hash($sock)] = [
                'user_id' => $user_id,
                'expires_at' => isset($user['exp']) ? $user['exp'] : strtotime('+1 hour'),
                'token' => $token,
                'subscriptions' => [
                    'user',
                    'user-' . $user_id
                ],
            ];

            socket_getpeername($sock, $ip);
            $this->broadcast("auth", ['user-' . $user_id], ['success' => true, "ip" => $ip]);
        } catch (Exception $e) {
            $this->broadcast("message", ["user"], ['success' => false, "message" => $e->getMessage()]);
        }
    }

    function process_sock_recv(Socket &$sock, array $data)
    {
        if (!isset($data['action'])) {
            return;
        }

        $action = $data['action'];
        match ($action) {
            'auth' => $this->process_sock_auth($sock, $data),
            default => null
        };
    }

    function broadcast(string $event, array $channels = [], array $data = [])
    {
        $data['event'] = $event;
        foreach ($this->users as $sock_hash => $user) {
            if (count($channels) && array_intersect($channels, $user['subscriptions'])) {
                $this->send_sock_message($this->clients[$sock_hash], $data);
            }
        }
    }

    public function run()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->address, $this->port);
        socket_listen($this->socket);
        $this->clients = [spl_object_hash($this->socket) => $this->socket];
        $this->users = [];

        echo "Websocket server started", PHP_EOL;
        echo "Listening on: $this->address:$this->port", PHP_EOL;

        while (true) {
            $read = $this->clients;
            $num_changed_sockets = socket_select($read, $write, $except, 0);

            if ($num_changed_sockets === false) {
                echo "socket_select() failed, reason: " . socket_strerror(socket_last_error()) . "\n";
                break;
            }

            if (in_array($this->socket, $read)) {
                $client = socket_accept($this->socket);
                $sock_hash = spl_object_hash($client);
                $this->clients[$sock_hash] = $client;

                $header = socket_read($client, 1024);
                $this->perform_handshaking($header, $client, $this->address, $this->port);
                socket_getpeername($client, $ip, $port);

                $this->send_sock_message($client, ['event' => 'connect', 'message' => $ip . ' connected']);
                echo "connected: $ip", PHP_EOL;
                unset($read[array_search($this->socket, $read)]);
            }

            foreach ($this->users as $sock_hash => $user) {
                if (isset($user['expires_at']) && intval($user['expires_at']) < time()) {
                    unset($this->users[$sock_hash]);
                    unset($this->clients[$sock_hash]);
                    unset($read[$sock_hash]);
                    echo "disconnected: $sock_hash", PHP_EOL;
                }
            }

            foreach ($read as $sock_hash => $sock) {
                $bytes = @socket_recv($sock, $buf, 1024, 0);
                $recv = $this->unmask($buf);
                $data = json_decode($recv, true);

                if ($bytes == 0 || empty($recv) || $data === null) {
                    socket_getpeername($sock, $ip);
                    if ($sock_hash) {
                        unset($this->clients[$sock_hash]);
                        unset($this->users[$sock_hash]);
                        echo "disconnected: $ip $sock_hash", PHP_EOL;
                    }
                } else if ($data) {
                    $this->process_sock_recv($sock, $data);
                }
            }
        }
    }
}
