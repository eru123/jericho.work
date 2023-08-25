<?php

namespace App\Commands;

use App\Plugin\Command as PluginCommand;

abstract class Command
{
    protected $name = '';
    protected $description = '';
    protected $help = '';
    protected $args = [];

    public function __construct()
    {
    }
}
