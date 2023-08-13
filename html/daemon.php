<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Daemon;

$d = new Daemon();
$d->us(10000);
$d->mem_limit('128M');

$d->run([
    function (Daemon $c) {
        if ($c->is_second_new()) {
            $date = date('Y-m-d H:i:s');
            $mem = rtrim(number_format($c->musage(), 2), '0.');
            $mac = $c->malloc();
            $cyc = $c->cycle();
            cmd(['mail_queues'], true);
            echo "[{$date}]\tcycle: {$cyc}\tmem: {$mem}/{$mac}MB\n";
        }
    },
    function (Daemon $c) {
        if ($c->is_second(0)) {
            echo "second 0\n";
        }
    }
]);
