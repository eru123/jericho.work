<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Mailer;

try {
    $processed = mailer::send_queues();
    writelog("[{$date}]\tProcessed Mail Queues: {$processed}");
} catch (Exception $e) {
    throw $e;
}
