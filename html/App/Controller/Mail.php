<?php

namespace App\Controller;

use eru123\router\Context;
use eru123\email\provider\SMTP;

class Mail extends Controller {
    public function guard(Context $c) {
        return null;
    }

    public function send(Context $c) {
        http_response_code(201);
        return [
            'success' => 'Mail sent successfully',
            'code' => 201,
        ];
    }

    public function public_mail_test(Context $c) {
        $raw = $c->json();
        $allowed = [
            'host',
            'provider', // smtp|gmail
            'username',
            'password',
            'port',
            'secure', // none|tls|ssl
            'from_name',
            'from_email',
            'subject',
            'to',
        ];

        $data = array_intersect_key($raw, array_flip($allowed));
        if ($data['provider'] === 'gmail') {
            $data['secure'] = 'ssl';
            $data['port'] = 465;
            $data['host'] = 'smtp.gmail.com';
            $data['from_email'] = $data['username'];
        }

        foreach ($allowed as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                http_response_code(400);
                return [
                    'error' => 'Missing required field: ' . $key,
                ];
            }
        }

        $email = $data['to'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return [
                'error' => 'Invalid recipient email address',
            ];
        }

        $body = $raw['body'] ?? "Hello,\n\nThis is a test email from the API.\nThis is auto-generated, please do not reply.\n\nThanks,\nSKIDDPH SMTP Tester";
        $data['debug'] = true;
        $data['debug_sapi'] = 'cli';
        $data['debug_color'] = false;
        $data['port'] = (int) $data['port'];
        $smtp = new SMTP($data);
        
        $logs = null;
        $sent = false;
        ob_start();
        $sent = $smtp->send(['to' => [$email], 'subject' => $data['subject'], 'body' => $body]);
        $logs = ob_get_contents();
        ob_end_clean();

        $id = $smtp->id();
        $provider = $smtp->provider();
        $recv_logs = $smtp->logs();

        if ($sent) {
            http_response_code(200);
            return [
                'success' => 'Mail sent successfully',
                'id' => $id,
                'provider' => $provider,
                'logs' => $logs,
                'recv_logs' => $recv_logs,
            ];
        } else {
            http_response_code(200);
            return [
                'error' => 'Failed to send mail',
                'id' => $id,
                'provider' => $provider,
                'logs' => $logs,
                'recv_logs' => $recv_logs,
            ];
        }
    }
}