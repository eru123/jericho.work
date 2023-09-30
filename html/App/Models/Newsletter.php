<?php

namespace App\Models;

class Newsletter extends AbstractModel
{   
    protected static $table = 'newsletter';
    protected static $allowed = [
        'email',
        'subscriptions',
        'verified',
        'disabled_at'
    ];

    protected static $created_at = 'created_at';
    protected static $updated_at = 'updated_at';
    protected static $deleted_at = 'deleted_at';
    protected static $disabled_at = 'disabled_at';
    protected static $primary_key = 'id';

    protected static $date_format = 'Y-m-d H:i:s';
    protected static $soft_delete = true;
    protected static $use_created_at = true;
    protected static $use_updated_at = true;
}