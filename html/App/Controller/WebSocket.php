<?php

namespace App\Controller;

use Socket;

class WebSocket
{
    private $socket;
    private $clients = [];
    private $changed;
    private $users = [];
    private $address;
    private $port;
    private $write;
    private $except;

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

    public function run()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->address, $this->port);
        socket_listen($this->socket);
        $this->clients = [$this->socket];
        $this->users = [];

        echo "Server started\n";
        echo "Listening on: $this->address:$this->port\n";
        // $this->write = NULL;
        // $this->except = NULL;
        while (true) {
            $read = $this->clients;

            $num_changed_sockets = socket_select($read, $write, $except, 0);

            if ($num_changed_sockets === false) {
                echo "socket_select() failed, reason: " . socket_strerror(socket_last_error()) . "\n";
                break;
            }

            if (in_array($this->socket, $read)) {
                $this->clients[] = $client = socket_accept($this->socket);

                $header = socket_read($client, 1024);
                $this->perform_handshaking($header, $client, $this->address, $this->port);
                socket_getpeername($client, $ip, $port);

                $this->send_sock_message($client, ['event' => 'connect', 'message' => $ip . ' connected']);
                echo "connected: $ip\n";

                unset($read[array_search($this->socket, $read)]);
            }

            foreach ($read as $sock) {
                $bytes = @socket_recv($sock, $buf, 1024, 0);
                $recv = $this->unmask($buf);
                $data = json_decode($recv, true);
                
                if ($bytes == 0 || empty($recv) || $data === null) {
                    socket_getpeername($sock, $ip);
                    $index = array_search($sock, $this->clients);
                    unset($this->clients[$index]);
                    echo "disconnected: $ip $index\n";
                    $this->send_message(['event' => 'disconnect', 'message' => $ip . ' disconnected']);
                } else {
                    echo "recv$ ", $recv . "\n";
                    $this->send_message($data ?? []);
                }

                echo "keys: " . implode(', ', array_keys($this->clients)) . "\n";
            }

        }
        $this->run();
    }
}
