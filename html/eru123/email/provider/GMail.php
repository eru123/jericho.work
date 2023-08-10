<?php

namespace eru123\email\provider;

class GMail extends SMTP implements OutboundInterface
{
    public function __construct(array $config = [])
    {
        $cfg = array_merge([
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'secure' => 'ssl',
        ], $config);
        parent::__construct($cfg);
    }
}
