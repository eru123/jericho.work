<?php

namespace eru123\router;

use Exception;

class Response
{
    protected $code = 200;
    protected $handler;
    protected $context = null;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function set_handler(callable $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    public function status(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function http(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function code(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function send($body): void
    {
        if (!headers_sent()) {
            http_response_code($this->code);
        }
        $f = $this->handler;

        if (!is_callable($f)) {
            throw new Exception('Response handler is not callable');
        }
        $f($body);
        exit;
    }
}
