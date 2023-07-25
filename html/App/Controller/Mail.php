<?php

namespace App\Controller;

use App\Plugin\Upload;
use App\Plugin\R2;
use App\Plugin\DB;
use App\Plugin\Rate;
use App\Plugin\Mail as Mailer;
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
}