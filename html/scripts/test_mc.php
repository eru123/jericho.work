<?php
require_once __DIR__ . '/autoload.php';

use App\Plugin\MC;

$mc = MC::instance();
for ($i = 0; $i < 10; $i++) {
    echo "Last OTP Code: " . $mc->get('otp:code') . PHP_EOL;
    $code = rand(100000, 999999);
    echo "OTP Code: {$code}\n";
    $mc->set("otp:code", $code, 60);
}
