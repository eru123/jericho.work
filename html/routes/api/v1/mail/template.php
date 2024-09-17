<?php

use eru123\router\Router;

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

return $mailTemplate;