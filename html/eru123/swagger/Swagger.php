<?php

namespace eru123\swagger;

use eru123\router\Router;

class Swagger
{
    /**
     * Create Swagger Routes
     * @param array $cfg
     * @return Router
     */
    public static function build($cfg)
    {
        $routes = static::map_cfg($cfg);
        $routes = array_map('realpath', $routes);
        $routes = array_filter($routes, 'is_file');

        ksort($routes);
        uksort($routes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        $router = new Router();
        foreach ($routes as $route => $file) {
            $router->get(rtrim($route, '/') . '/swagger.json', function () use ($file) {
                $handle = fopen($file, 'r');
                while (!feof($handle)) {
                    print fread($handle, 4096);
                }
                fclose($handle);
                exit;
            });
            $router->get('/' . trim($route, '/'), function () use ($route) {
                $loc = explode('/', $route);
                $loc = end($loc) . '/index.html';
                header('Location: ' . $loc);
                exit;
            });
            $router->static('/' . trim($route, '/') .'/', __DIR__ . '/public');
        }
        return $router;
    }

    /**
     * Convert cfg to 2d array
     * @param array $cfg
     * @param string $prefix
     * @return array
     */
    private static function map_cfg($cfg, $prefix = '')
    {
        $routes = [];

        foreach ($cfg as $key => $value) {
            if (is_array($value)) {
                $routes = array_merge($routes, static::map_cfg($value, $prefix . $key));
            } else if (is_string($value)) {
                $routes[$prefix . $key] = $value;
            }
        }

        return $routes;
    }
}
