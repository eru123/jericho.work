<?php

use eru123\router\Router;
use eru123\net\Tools as NetTools;

$v1 = new Router();
$v1->base('/v1');
$v1->bootstrap([
    'App\Controller\Auth@bootstrap'
]);

$v1->get('/net/ip', fn() => NetTools::get_client_ip() ?: 'unknown');
$v1->get('/net/whoami', fn() => NetTools::whoami() ?: ['error' => 'Unknown error occured']);
$v1->post('/report', 'App\Controller\Analytics@report');
$v1->post('/newsletter/add', 'App\Controller\Newsletter@add');

$auth = require(__ROUTES__ . '/api/v1/auth.php');
$mail = require(__ROUTES__ . '/api/v1/mail/mail.php');
$mxtl = require(__ROUTES__ . '/api/v1/mail/template.php');
$mail->child($mxtl);

$v1->child($auth);
$v1->child($mail);
return $v1;
