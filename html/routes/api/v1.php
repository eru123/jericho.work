<?php

use eru123\router\Router;

// APIv1

$v1 = new Router();
$v1->base('/v1');
$v1->bootstrap([
    'App\Controller\Auth@bootstrap'
]);

// Auth

$auth = new Router();
$auth->base('/auth');
$auth->post('/register', 'App\Controller\Auth@register');
$auth->post('/login', 'App\Controller\Auth@login');
$auth->post('/logout', 'App\Controller\Auth@guard', 'App\Controller\Auth@logout');
$auth->post('/hello', 'App\Controller\Auth@guard', 'App\Controller\Auth@hello');
$auth->post('/update', 'App\Controller\Auth@guard', 'App\Controller\Auth@update');
$auth->post('/mail/add', 'App\Controller\Auth@guard', 'App\Controller\Verification@add_mail');
$auth->post('/mail/verify', 'App\Controller\Auth@guard', 'App\Controller\Verification@verify_mail');

// Mail

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

// Mail Template

$mailTemplate = new Router();
$mailTemplate->base('/template');
$mailTemplate->post('/create', 'App\Controller\MailTemplate@create');
$mailTemplate->post('/id/$id/view', 'App\Controller\MailTemplate@view');
$mailTemplate->post('/id/$id/update', 'App\Controller\MailTemplate@update');
$mailTemplate->post('/id/$id/delete', 'App\Controller\MailTemplate@delete');
$mailTemplate->post('/id/$id/trash', 'App\Controller\MailTemplate@view_deleted');
$mailTemplate->post('/id/$id/restore', 'App\Controller\MailTemplate@restore');
$mailTemplate->post('/code/$code/view', 'App\Controller\MailTemplate@view');
$mailTemplate->post('/code/$code/update', 'App\Controller\MailTemplate@update');
$mailTemplate->post('/code/$code/delete', 'App\Controller\MailTemplate@delete');
$mail->child($mailTemplate);

$v1->child($auth);
$v1->child($mail);
return $v1;
