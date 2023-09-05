<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Mailer;

try {
    $processed = mailer::send_queues();
    if (intval($processed) > 0) {
        writelog("Processed Mail Queues: {$processed}");
    }
} catch (Exception $e) {
    throw $e;
}
