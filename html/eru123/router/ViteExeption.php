<?php

namespace eru123\router;

use Exception;

class ViteExeption extends Exception
{
    public function __construct($message = "", $code = 0, $previous = null)
    {
        $message = json_encode($message);
        parent::__construct($message, $code, $previous);
    }
}
