<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Auth;

Auth::store_revoked_tokens();

writelog('Revoked tokens loaded');
