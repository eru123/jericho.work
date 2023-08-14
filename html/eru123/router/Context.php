<?php

namespace eru123\router;

use Exception;

/**
 * Context
 * 
 * @var array $route The current route data
 * @var array $routes The map of all routes
 * @var string $method The current request method
 * @var string $path The path definition of the current route
 * @var array $callbacks The callbacks of the current route
 * @var array $params The params of the current route
 * @var string|null $file The file of the current route if it's a static route, it's null if no file request or requested a directory
 * @var boolean $match If the current route is matched
 * @var boolean $matchdir If the current route is matched a directory
 */
class Context
{
    /**
     * @var array The data of the context
     */
    protected $__data__ = [];

    /**
     * @var Response The response instance
     */
    protected $__response__;

    public function __construct(array $data = [])
    {
        $this->__data__ = $data;
    }

    public function __set($name, $value)
    {
        if (in_array($name, ['res', 'resp', 'response']) || method_exists($this, $name)) {
            throw new Exception("Cannot override method $name");
        }

        $this->__data__[$name] = $value;
    }

    public function __get($name)
    {
        if (in_array($name, ['res', 'resp', 'response'])) {
            if (!$this->__response__) {
                $this->__response__ = new Response($this);
            }

            return $this->__response__;
        }

        return $this->__data__[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->__data__[$name]);
    }

    public function __unset($name)
    {
        unset($this->__data__[$name]);
    }

    public function __call($name, $args)
    {
        if (isset($this->__data__[$name]) && is_callable($this->__data__[$name])) {
            return call_user_func_array($this->__data__[$name], $args);
        }

        throw new Exception("Method $name not found");
    }

    public function __invoke($name, $args)
    {
        if (isset($this->__data__[$name]) && is_callable($this->__data__[$name])) {
            return call_user_func_array($this->__data__[$name], $args);
        }

        throw new Exception("Method $name not found");
    }

    public function __toString()
    {
        return json_encode($this->__data__);
    }

    public function __debugInfo()
    {
        return $this->__data__;
    }

    public function __toArray()
    {
        return $this->__data__;
    }

    public function __toObject()
    {
        return (object) $this->__data__;
    }

    public function __toStdClass()
    {
        return (object) $this->__data__;
    }

    public function body()
    {
        return file_get_contents('php://input');
    }

    public function json()
    {
        if (empty($this->body())) {
            return [];
        }

        return json_decode($this->body(), true) ?? [];
    }
}
