<?php

namespace eru123\router\static;

use eru123\router\Router as DynamicRouter;

/**
 * @method static DynamicRouter fallback($callback) Set the fallback callback
 * @method static DynamicRouter error($callback) Set the error callback
 * @method static DynamicRouter response($callback) Set the response callback
 * @method static DynamicRouter request(string $method, string $path, ...$callback) Define a route
 * @method static DynamicRouter route(string $method, string $path, ...$callback) Alias of request()
 * @method static DynamicRouter get(string $path, ...$callback) Define a GET route
 * @method static DynamicRouter post(string $path, ...$callback) Define a POST route
 * @method static DynamicRouter put(string $path, ...$callback) Define a PUT route
 * @method static DynamicRouter delete(string $path, ...$callback) Define a DELETE route
 * @method static DynamicRouter patch(string $path, ...$callback) Define a PATCH route
 * @method static DynamicRouter options(string $path, ...$callback) Define a OPTIONS route
 * @method static DynamicRouter head(string $path, ...$callback) Define a HEAD route
 * @method static DynamicRouter any(string $path, ...$callback) Define a route for all methods
 * @method static DynamicRouter static(string $path, string|array $dir, string|array $index = [], ...$callbacks) Define a static route
 * @method static DynamicRouter proxy(string $url, ...$callbacks) Define a proxy route
 * @method static DynamicRouter base(?string $base) Set the base path if $base is not null or return the base path
 * @method static DynamicRouter child(DynamicRouter $router) Add a child router
 * @method static DynamicRouter parent(?DynamicRouter $router) Set the parent router if $router is not null or return the parent router
 * @method static DynamicRouter bootstrap(array|callable $callbacks) Set the bootstrap callback
 * @method static array map(string $parent_base = '', array $parent_callbacks = []) Get the map of the router
 * @method static void run(?string $base = null) Run the router
 * @method static void status_page(int $code, string $title, string $message) Response with a status page
 */
class Router
{
    /**
     * @var DynamicRouter The router instance
     */
    protected static $router;

    /**
     * Get the router instance, if not exists, create one
     * @return DynamicRouter
     */
    final protected static function getRouter()
    {
        if (!isset(self::$router)) {
            self::$router = new DynamicRouter;
        }
        return self::$router;
    }

    /**
     * Call the router instance methods statically
     * @param string $name Name of the method from eru123\router\Router
     * @param array $args Arguments to pass to the method 
     * @return mixed
     */
    final public static function __callStatic($name, $args)
    {
        return self::getRouter()->$name(...$args);
    }
}
