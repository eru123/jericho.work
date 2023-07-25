<?php

use eru123\router\Router;

$v1 = new Router();
$v1->base('/v1');

$mail = new Router();
$mail->base('/mail');
$mail->bootstrap([
    'App\Controller\Mail@guard'
]);

$mail->post('/create', 'App\Controller\Mail@send');
// $mail->post('/view/$id', 'App\Controller\Mail@get');
// $mail->post('/update/$id', 'App\Controller\Mail@update');
// $mail->post('/cancel/$id', 'App\Controller\Mail@cancel');
// $mail->post('/delete/$id', 'App\Controller\Mail@delete');
// $mail->post('/list', 'App\Controller\Mail@list');

$v1->child($mail);
return $v1;
