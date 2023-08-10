<?php

namespace eru123\router;

use Exception;
use Error;
use Throwable;
use InvalidArgumentException;
use eru123\fs\File;
use eru123\http\Fetch;

class Router
{
    protected $childs = [];
    protected $parent = null;
    protected $routes = [];
    protected $bootstraps = [];
    protected $base = '';
    protected $fallback = null;
    protected $error = null;
    protected $response = null;

    public function __construct(?Router $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Set a callback function to be called if no route is matched
     * @param callable|null|string $callback
     * @return Router|callable|null
     */
    public function fallback($callback = null): static|callable|null|string
    {
        if (is_null($callback)) {
            return $this->fallback ? $this->fallback : ($this->parent ? $this->parent->fallback() : null);
        }
        $this->fallback = $callback;
        return $this;
    }

    /**
     * Convert to a recognizable callback
     * @param callable|string|array|Closure $callback
     * @return callable
     */
    public function make_callable($cb)
    {
        if (is_callable($cb)) {
            return $cb;
        }

        if (is_string($cb)) {
            $rgx = '/^([a-zA-Z0-9_\\\\]+)(::|@)([a-zA-Z0-9_]+)$/';
            if (preg_match($rgx, $cb, $matches)) {
                $classname = $matches[1];
                $method = $matches[3];
                if (class_exists($classname)) {
                    $obj = new $classname();
                    if (method_exists($obj, $method)) {
                        return [$obj, $method];
                    }
                }
            }
        }

        if (is_array($cb) && count($cb) == 2) {
            if (is_object($cb[0]) && is_string($cb[1])) {
                return $cb;
            } else if (is_string($cb[0]) && is_string($cb[1])) {
                $classname = $cb[0];
                $method = $cb[1];
                if (class_exists($classname)) {
                    $obj = new $classname();
                    if (method_exists($obj, $method)) {
                        return [$obj, $method];
                    } else if (method_exists($classname, $method)) {
                        return $cb;
                    }
                }
            }
        }

        throw new Exception('invalid callback');
    }

    /**
     * Check if has a fallback callback
     * @return boolean
     */
    public function has_fallback(): bool
    {
        return !is_null($this->fallback);
    }

    /**
     * Set a callback function to be called if an error is thrown
     * @param callable|null|string $callback
     * @return Router|callable|null
     */
    public function error(callable $callback = null): static|callable|null|string
    {
        if (is_null($callback)) {
            return $this->error ? $this->error : ($this->parent ? $this->parent->error() : null);
        }

        $this->error = $callback;
        return $this;
    }

    /**
     * Set a callback function to be called if a response is returned
     * @param callable|null|string $callback
     * @return Router|callable|null
     */
    public function response(callable $callback = null): static|callable|null|string
    {
        if (is_null($callback)) {
            return $this->response ? $this->response : ($this->parent ? $this->parent->response() : null);
        }
        $this->response = $callback;
        return $this;
    }

    /**
     * Define a route
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, ANY, STATIC)
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function request($method, $url, ...$callbacks): static
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $url,
            'callbacks' => $callbacks
        ];
        return $this;
    }

    /**
     * Alias of request()
     * @param string $method HTTP method (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, ANY, STATIC)
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function route(...$args): static
    {
        return $this->request(...$args);
    }

    /**
     * Define a route with GET method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function get($url, ...$callbacks): static
    {
        return $this->request('GET', $url, ...$callbacks);
    }

    /**
     * Define a route with POST method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function post($url, ...$callbacks): static
    {
        return $this->request('POST', $url, ...$callbacks);
    }

    /**
     * Define a route bootstrapper function, it will be called before any route callbacks are called
     * @param callable|array $callbacks
     * @return Router
     */
    public function bootstrap(array|callable $callbacks): static
    {
        if (is_array($callbacks)) {
            $this->bootstraps = array_merge($this->bootstraps, $callbacks);
        } else {
            $this->bootstraps[] = $callbacks;
        }

        return $this;
    }

    /**
     * Define a route with PUT method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function put($url, ...$callbacks): static
    {
        return $this->request('PUT', $url, ...$callbacks);
    }

    /**
     * Define a route with DELETE method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function delete($url, ...$callbacks): static
    {
        return $this->request('DELETE', $url, ...$callbacks);
    }

    /**
     * Define a route with PATCH method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function patch($url, ...$callbacks): static
    {
        return $this->request('PATCH', $url, ...$callbacks);
    }

    /**
     * Define a route with OPTIONS method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function options($url, ...$callbacks): static
    {
        return $this->request('OPTIONS', $url, ...$callbacks);
    }

    /**
     * Define a route with HEAD method
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function head($url, ...$callbacks): static
    {
        return $this->request('HEAD', $url, ...$callbacks);
    }

    /**
     * Define a route with ANY method. The route will be matched with any HTTP method.
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function any($url, ...$callbacks): static
    {
        return $this->request('ANY', $url, ...$callbacks);
    }

    /**
     * Define a route with STATIC method. The route will be matched with any HTTP method.
     * @param string $url URL path to match
     * @param string|array $dir Directory or directories to serve static files from
     * @param string|array $index Index file or files to serve if the directory is requested
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function static($url, string|array $dir, string|array $index = [], ...$callbacks): static
    {
        if (is_string($dir)) {
            $dir = [$dir];
        }

        if (is_string($index)) {
            $index = [$index];
        }

        $precallback = function (Context $context) use ($dir, $index) {
            $context->file_path = null;

            if (!isset($context->route['file']) || empty($context->route['file'])) {
                return null;
            }

            $fp = ltrim(urldecode($context->route['file']), '/');

            foreach ($dir as $i => $d) {
                $d = realpath($d);
                $f = null;

                if (!$d) {
                    continue;
                }

                if (!empty($fp)) {
                    $f = realpath($d . '/' . $fp);
                    if (!$f) {
                        continue;
                    }
                }

                if (!$f) {
                    foreach ($index as $j => $f) {
                        $f = realpath($d . '/' . $f);
                        if ($f) {
                            break;
                        }
                    }
                }

                if (!$f || !file_exists($f) || strpos($f, $d) !== 0) {
                    continue;
                }

                $context->file_path = $f;
                return null;
            }
        };

        $postcallback = function (Context $context) {
            if ($context->file_path) {
                return (new File($context->file_path))->stream();
            }
        };

        array_unshift($callbacks, $precallback);
        array_push($callbacks, $postcallback);

        return $this->request('STATIC', $url, ...$callbacks);
    }

    /**
     * Define a route with PROXY method. The route will be matched with any HTTP/S method.
     * Please note that this is intended for API forwarding to bypass CORS restrictions and does not modify the response body. Use callbacks to validate the request. 
     * @param string $url URL path to match
     * @param callable ...$callbacks Callback functions to be called if the route is matched
     * @return Router
     */
    public function proxy($url, ...$callbacks): static
    {
        $callbacks[] = function (Context $context) {
            if ($context->route['method'] !== 'PROXY' || !$context->route['matchdir'] || empty($context->route['file'])) {
                return null;
            }

            $fp = ltrim(urldecode($context->route['file']), '/');
            return Fetch::httpForwardedTo($fp);
        };

        return $this->request('PROXY', $url, ...$callbacks);
    }

    /**
     * Add a router group
     * @param Router $router
     * @return Router
     */
    public function child(Router $router): static
    {
        if ($router === $this) {
            throw new InvalidArgumentException('Cannot add router to itself');
        }
        $router->parent($this);
        $this->childs[] = $router;
        return $this;
    }

    /**
     * Get the parent router, or set the parent router
     * @param Router|null $router The parent router to set, or null to get the parent router
     * @return Router|null
     */
    public function parent(?Router $router): ?Router
    {
        return is_null($router) ? $this->parent : $this->parent = $router;
    }

    /**
     * Get the base path, or set the base path
     * @param string|null $base The base path to set, or null to get the base path
     * @return string
     */
    public function base(?string $base = null): string
    {
        return is_null($base) ? $this->base : $this->base = $base;
    }

    /**
     * Map the router to an array of routes including all child routers
     * @param string $parent_base The base path of the parent router
     * @param array $parent_callbacks The callbacks of the parent router (bootstrap callbacks)
     * @return array
     */
    public function map(string $parent_base = '', array $parent_callbacks = []): array
    {
        $map = [];
        $fallbacks = [];
        $stack = [[$this, $parent_base, $parent_callbacks]];

        while (!empty($stack)) {
            [$router, $prefix, $callbacks] = array_pop($stack);

            $static = [];
            foreach ($router->routes as $route) {
                if ($route['method'] == 'STATIC') {
                    $static[] = [
                        'router' => $router,
                        'method' => strtoupper(trim($route['method'])),
                        'path' => $prefix . $router->base() . $route['path'],
                        'callbacks' => array_merge($callbacks, $router->bootstraps, $route['callbacks'])
                    ];
                    continue;
                }
                $map[] = [
                    'router' => $router,
                    'method' => strtoupper(trim($route['method'])),
                    'path' => $prefix . $router->base() . $route['path'],
                    'callbacks' => array_merge($callbacks, $router->bootstraps, $route['callbacks'])
                ];
            }

            foreach ($static as $route) {
                $map[] = $route;
            }

            if ($router->has_fallback()) {
                $fallback = $prefix . $router->base();
                $fallbacks[] = [
                    'router' => $router,
                    'method' => 'FALLBACK',
                    'path' => $fallback,
                    'callbacks' => array_merge($callbacks, $router->bootstraps, [$router->fallback()]),
                ];
            }

            foreach ($router->childs as $child) {
                $stack[] = [$child, $prefix . $router->base(), array_merge($callbacks, $router->bootstraps)];
            }
        }

        $fallbacks = array_reverse($fallbacks);
        $map = array_merge($map, $fallbacks);

        return array_map(function ($route) {
            $route['fallback'] = Helper::match_fallback($route['path']);
            $route['match'] = Helper::match($route['path']);
            $route['matchdir'] = Helper::matchdir($route['path']);
            $route['params'] = Helper::params($route['path']);
            $route['file'] = Helper::file($route['path']);
            $route['params'] = array_merge($route['params']);
            $route['fallback_params'] = Helper::fallback_params($route['path']);
            return $route;
        }, $map);
    }

    /**
     * Create child router as a group
     * @param string $base The base path of the child router
     * @return Router
     */
    public function group(string $base = null): Router
    {
        $router = new Router();
        is_null($base) || $router->base($base);
        $this->child($router);
        return $router;
    }


    /**
     * Default HTML Page for handling errors, exceptions and fallbacks
     * @param int $code HTTP status code
     * @param string $title Page title
     * @param string $message Page message
     * @return void
     */
    public static function status_page(int $code, string $title, string $message): void
    {
        http_response_code($code);
        $title = htmlspecialchars($title);
        echo "<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>$title</title> <style> body { font-family: sans-serif; background-color: #f1f1f1; } h1 { text-align: center; margin-top: 100px; } p { text-align: center; font-size: 18px; } a { color: #1e87dc; text-decoration: none; } a:hover { color: #186eb4 } </style></head><body><h1>$title</h1><p>$message</p></body></html>";
        exit;
    }

    /**
     * Run the router
     * @param string|null $base The base path to set, or null to use the current base path
     * @return void
     */
    public function run(?string $base = null): void
    {
        if (!is_null($base)) {
            $this->base($base);
        }

        $map = $this->map();

        $default_fallback_handler = !empty($this->fallback) ? $this->fallback : function () {
            return self::status_page(404, '404 Not Found', 'The requested URL was not found on this server.');
        };

        $default_error_handler = !empty($this->error) ? $this->error : function (Throwable $e) {
            return self::status_page(500, '500 Internal Server Error', 'The server encountered an internal error and was unable to complete your request. Either the server is overloaded or there is an error in the application.');
        };

        $default_response_handler = !empty($this->response) ? $this->response : function ($response) {
            if (is_array($response) || is_object($response)) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else if (is_string($response) || is_numeric($response)) {
                echo $response;
                exit;
            }
        };

        $fallback_handler = $default_fallback_handler;
        $error_handler = $default_error_handler;
        $response_handler = $default_response_handler;

        $context = new Context();
        $context->route = null;
        $context->routes = $map;
        $context->response->set_handler($response_handler);

        try {
            $callback_response = null;
            foreach ($map as $route) {
                $fallback_handler = $this->make_callable($route['router']->fallback() ?? $default_fallback_handler);
                $error_handler = $this->make_callable($route['router']->error() ?? $default_error_handler);
                $response_handler = $this->make_callable($route['router']->response() ?? $default_response_handler);

                if ($route['match'] || $route['matchdir']) {
                    $context = new Context($route);
                    $context->route = $route;
                    $context->routes = $map;
                    $context->response->set_handler($response_handler);
                    $callbacks = $route['callbacks'];

                    $match_any = $route['method'] == 'ANY';
                    $match_method = $route['method'] == Helper::method();
                    $match_url = ($match_any || $match_method) && $route['match'];
                    $match_dir = $route['method'] == 'STATIC' && $route['matchdir'];
                    $match_proxy = $context->route['method'] == 'PROXY' && isset($context->route['params']['file']) && !empty($context->route['params']['file']);
                    $match_fallback = $context->route['method'] == 'FALLBACK' && $route['fallback'];

                    if ($match_url || $match_dir || $match_proxy || $match_fallback) {
                        $callback_response = null;
                        while (!empty($callbacks) && is_null($callback_response) && $callback_response !== false) {
                            $callback = $this->make_callable(array_shift($callbacks));
                            if (is_callable($callback)) {
                                $callback_response = call_user_func_array($callback, [&$context]);
                            }
                        }

                        if (!is_null($callback_response)) {
                            $response_handler($callback_response);
                        }
                    }
                }
            }

            if (is_null($callback_response) || $callback_response === false) {
                $response_handler($fallback_handler($context));
            }
        } catch (Exception $e) {
            $response_handler($error_handler($e, $context));
        } catch (Error $e) {
            $response_handler($error_handler($e, $context));
        } catch (Throwable $e) {
            $response_handler($error_handler($e, $context));
        }
    }
}
