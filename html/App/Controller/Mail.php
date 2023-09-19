<?php

namespace App\Controller;

use eru123\router\Context;

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
            'provider', // custom|gmail
            'username',
            'password',
            'port',
            'secure', // false|tls|ssl
            'from_name',
            'from_email',
        ];
        // get all data from $raw that is in $allowed
        $data = array_intersect_key($raw, array_flip($allowed));
        return $data;
    }
}