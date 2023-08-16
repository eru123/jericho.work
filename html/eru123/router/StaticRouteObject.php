<?php

namespace eru123\router;

class StaticRouteObject
{
    private $data = [];
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array()
    {
        return $this->data;
    }
}
