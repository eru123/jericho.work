<?php

namespace App\Controller;

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

    function send_message($msg)
    {
        foreach ($this->clients as $changed_socket) {
            @socket_write($changed_socket, $msg, strlen($msg));
        }
        return true;
    }

    function unmask($text)
    {
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

        while (true) {
            $this->changed = $this->clients;
            $this->write = NULL;
            $this->except = NULL;
            socket_select($this->changed, $this->write, $this->except, NULL);

            foreach ($this->changed as $changed_socket) {
                if ($changed_socket == $this->socket) {
                    $client = socket_accept($this->socket);
                    $this->clients[] = $client;

                    $header = socket_read($client, 1024);
                    $this->perform_handshaking($header, $client, $this->address, $this->port);
                    echo "Client connected: " . json_encode($header) . "\n";
                    socket_getpeername($client, $ip);

                    $users[] = $client;

                    $response = $this->mask(json_encode(['type' => 'system', 'message' => $ip . ' connected']));
                    $this->send_message($response);
                } else {
                    $bytes = @socket_recv($changed_socket, $buf, 1024, 0);
                    if ($bytes == 0) {
                        socket_getpeername($changed_socket, $ip);
                        unset($users[$ip]);

                        $index = array_search($changed_socket, $this->clients);
                        unset($this->clients[$index]);

                        $response = $this->mask(json_encode(['type' => 'system', 'message' => $ip . ' disconnected']));
                        $this->send_message($response);
                    } else {
                        $received_text = $this->unmask($buf);
                        echo "rec> ", $received_text . "\n";
                        $tst_msg = json_decode($received_text);
                        $user_name = @$tst_msg?->name;
                        $user_message = @$tst_msg?->message;
                        $user_color = @$tst_msg?->color;

                        $response_text = $this->mask(json_encode(['type' => 'usermsg', 'name' => $user_name, 'message' => $user_message, 'color' => $user_color]));
                        $this->send_message($response_text);
                    }
                }
            }
        }
    }
}
