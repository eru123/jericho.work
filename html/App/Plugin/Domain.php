<?php

namespace App\Plugin;

use eru123\router\Router;

class Domain
{
    static $instance = null;
    private $vhosts = [];
    private $dir = null;

    public static function instance(string $dir = null)
    {
        if (static::$instance === null) {
            static::$instance = new static($dir);
        }
        return static::$instance;
    }

    private function __construct(?string $dir)
    {
        $f = realpath($dir);
        if (!$f || !is_dir($f)) {
            Router::status_page(500, 'Internal Server Error', "Domain directory not found: $dir");
        }

        $this->dir = $f;
    }

    public function createVirtualHost(?string $domain, string $record = null)
    {
        if (empty($domain)) {
            return;
        }

        $domain = strtolower($domain);
        if (!preg_match('/^(([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}|localhost)$/', $domain)) {
            Router::status_page(500, 'Internal Server Error', "Invalid domain name: \"$domain\"");
        }

        $rec = !is_null($record) ? realpath($this->dir . "/$record.domain.php") : realpath($this->dir . "/domain.php");
        if (!$rec || is_dir($rec)) {
            Router::status_page(500, 'Internal Server Error', "Record not found: \"$record\"");
        }

        $router = require($rec);
        if (!($router instanceof Router)) {
            Router::status_page(500, 'Internal Server Error', "Record must return instance of " . Router::class);
        }

        $this->vhosts[] = [
            'domain' => $domain,
            'record' => $rec,
            'router' => &$router,
        ];
    }

    public function setDefaultHandler(string $record)
    {
        $rec = realpath($this->dir . "/$record.domain.php");
        if (!$rec || is_dir($rec)) {
            Router::status_page(500, 'Internal Server Error', "Record not found: \"$record\"");
        }

        $router = require($rec);
        if (!($router instanceof Router)) {
            Router::status_page(500, 'Internal Server Error', "Record must return instance of " . Router::class);
        }

        $this->vhosts[] = [
            'domain' => null,
            'record' => $rec,
            'router' => &$router,
        ];
    } 

    public function serve()
    {
        if (empty($this->vhosts)) {
            Router::status_page(500, 'Internal Server Error', "No virtual host found");
        }

        out($this->vhosts);

        $sname = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost';
        $sname = strtolower($sname);

        $default_handler = null;

        foreach ($this->vhosts as $vhost) {
            if (is_null($vhost['domain'])) {
                $default_handler = $vhost;
                continue;
            } else if ($vhost['domain'] == $sname) {
                $vhost['router']->run();
                exit;
            }
        }

        if (!is_null($default_handler)) {
            $default_handler['router']->run();
            exit;
        }

        $f = realpath($this->dir . "/domain.php");
        $router = new Router();

        if (!$f || is_dir($f)) {
            Router::status_page(500, 'Internal Server Error', 'Default domain record not found');
        } else {
            $router = require($f);
            if (!($router instanceof Router)) {
                Router::status_page(500, 'Internal Server Error', 'Default domain record must return instance of ' . Router::class);
            }
        }

        $router->run();
        exit;
    }
}
