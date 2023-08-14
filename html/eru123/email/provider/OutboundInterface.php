<?php

namespace eru123\email\provider;

interface OutboundInterface
{
    public function __construct(array $config);
    public function send(array $data): bool;
}
