<?php

namespace App\Models;

class Newsletter extends AbstractModel
{   
    protected static $table = 'newsletter';
    protected static $allowed = [];
    protected static $created_at = false;
    protected static $updated_at = false;
    protected static $deleted_at = false;
    protected static $disabled_at = false;
    protected static $primary_key = false;

    protected static $date_format = 'Y-m-d H:i:s';
    protected static $soft_delete = false;
    protected static $use_created_at = false;
    protected static $use_updated_at = false;
}