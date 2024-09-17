<?php

use eru123\router\Router;

$auth = new Router();
$auth->base('/auth');
$auth->post('/register', 'App\Controller\Auth@register');
$auth->post('/login', 'App\Controller\Auth@login');
$auth->post('/logout', 'App\Controller\Auth@guard', 'App\Controller\Auth@logout');
$auth->post('/hello', 'App\Controller\Auth@guard', 'App\Controller\Auth@hello');
$auth->post('/update', 'App\Controller\Auth@guard', 'App\Controller\Auth@update');
$auth->post('/mail/add', 'App\Controller\Auth@guard', 'App\Controller\Verification@add_mail');
$auth->post('/mail/verify', 'App\Controller\Auth@guard', 'App\Controller\Verification@verify_mail');

return $auth;