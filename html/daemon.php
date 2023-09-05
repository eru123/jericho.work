<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Daemon;

$d = new Daemon();
$d->us(10000);
$d->mem_limit('128M');

$d->run([
    function (Daemon $c) {
        if ($c->is_second_new()) {
            cmd(['mail_queues'], true);
        }
    }
]);
